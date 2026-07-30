<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelInfo;
use Illuminate\Database\Eloquent\ModelInspector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use ReflectionClass;
use Victormgomes\LaravelQueryEngine\Attributes\QueryOptions;
use Victormgomes\LaravelQueryEngine\Enums\AbstractType;
use Victormgomes\LaravelQueryEngine\Support\Resource\FieldGenerator;
use Victormgomes\LaravelQueryEngine\Support\Resource\FilterConfig;
use Victormgomes\LaravelQueryEngine\Support\Resource\FilterGenerator;
use Victormgomes\LaravelQueryEngine\Support\Resource\IncludeConfig;
use Victormgomes\LaravelQueryEngine\Support\Resource\IncludeGenerator;
use Victormgomes\LaravelQueryEngine\Support\Resource\PaginationConfig;
use Victormgomes\LaravelQueryEngine\Support\Resource\PaginationGenerator;
use Victormgomes\LaravelQueryEngine\Support\Resource\ResourceConfig;
use Victormgomes\LaravelQueryEngine\Support\Resource\SortConfig;
use Victormgomes\LaravelQueryEngine\Support\Resource\SortGenerator;

class Resource
{
    /** @var array<string, ResourceConfig> */
    private static array $cache = [];

    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * @param  class-string<Model>  $modelFQCN
     * @param  string|null  $connection
     */
    public static function generate(string $modelFQCN, $connection = null): ResourceConfig
    {
        if (isset(self::$cache[$modelFQCN])) {
            return self::$cache[$modelFQCN];
        }

        /** @var Model $modelInstance */
        $modelInstance = new $modelFQCN;
        $visible = array_values($modelInstance->getVisible());
        $hidden = array_values($modelInstance->getHidden());

        $customConnection = Config::get('laravel-query-engine.metadata_connection');
        $connection = is_string($customConnection) ? $customConnection : null;

        $attributes = self::filterAttributesByVisibleAndHidden(
            self::normalizeModelInspectorAttributes($modelFQCN, $connection),
            $visible,
            $hidden,
        );

        $relationMap = RelationMapper::getMap($modelFQCN);

        $reflection = new ReflectionClass($modelFQCN);
        $attributesList = $reflection->getAttributes(QueryOptions::class);
        $modelConfig = ! empty($attributesList) ? $attributesList[0]->newInstance() : null;

        /** @var array<string, bool> $features */
        $features = Config::get('laravel-query-engine.features', [
            'filters' => true,
            'sorts' => true,
            'includes' => true,
            'fields' => true,
            'page' => true,
        ]);

        if ($modelConfig) {
            if ($modelConfig->filters !== null) {
                $features['filters'] = $modelConfig->filters;
            }
            if ($modelConfig->sorts !== null) {
                $features['sorts'] = $modelConfig->sorts;
            }
            if ($modelConfig->includes !== null) {
                $features['includes'] = $modelConfig->includes;
            }
            if ($modelConfig->fields !== null) {
                $features['fields'] = $modelConfig->fields;
            }
            if ($modelConfig->page !== null) {
                $features['page'] = $modelConfig->page;
            }
        }

        $allowedFilters = $modelConfig ? $modelConfig->allowedFilters : null;
        $disabledFilters = $modelConfig ? ($modelConfig->disableFilters ?? []) : [];
        $allowedSorts = $modelConfig ? $modelConfig->allowedSorts : null;
        $disabledSorts = $modelConfig ? ($modelConfig->disableSorts ?? []) : [];
        $allowedIncludes = $modelConfig ? $modelConfig->allowedIncludes : null;
        $disabledIncludes = $modelConfig ? ($modelConfig->disableIncludes ?? []) : [];
        $allowedFields = $modelConfig ? $modelConfig->allowedFields : null;
        $disabledFields = $modelConfig ? ($modelConfig->disableFields ?? []) : [];
        $allowedScopes = $modelConfig ? $modelConfig->allowedScopes : [];
        $allowedAggregations = $modelConfig ? $modelConfig->allowedAggregations : [];

        /** @var list<string> $availableColumns */
        $availableColumns = array_column($attributes, 'name');
        $availableRelations = array_keys($relationMap);
        /** @var list<string> $allValidFields */
        $allValidFields = array_merge($availableColumns, $availableRelations);

        self::validateConfigFields($modelFQCN, array_values($allowedFilters ?? []), $allValidFields, 'filters (allowed)');
        self::validateConfigFields($modelFQCN, array_values($disabledFilters), $allValidFields, 'filters (disabled)');
        self::validateConfigFields($modelFQCN, array_values($allowedSorts ?? []), $allValidFields, 'sorts (allowed)');
        self::validateConfigFields($modelFQCN, array_values($disabledSorts), $allValidFields, 'sorts (disabled)');
        self::validateConfigFields($modelFQCN, array_values($allowedIncludes ?? []), $availableRelations, 'includes (allowed)');
        self::validateConfigFields($modelFQCN, array_values($disabledIncludes), $availableRelations, 'includes (disabled)');
        self::validateConfigFields($modelFQCN, array_values($allowedFields ?? []), $availableColumns, 'fields (allowed)');
        self::validateConfigFields($modelFQCN, array_values($disabledFields), $availableColumns, 'fields (disabled)');

        return self::$cache[$modelFQCN] = new ResourceConfig(
            filters: ($features['filters'] ?? true) ? FilterGenerator::generate($attributes, $relationMap, $modelFQCN, $allowedFilters, $disabledFilters, $modelConfig?->allowedOperators, $modelConfig?->disableOperators, $allowedScopes) : [],
            sorts: ($features['sorts'] ?? true) ? SortGenerator::generate($attributes, $relationMap, $allowedSorts, $disabledSorts) : [],
            pagination: ($features['page'] ?? true) ? PaginationGenerator::generate() : new PaginationConfig(keys: [], defaults: []),
            fields: ($features['fields'] ?? true) ? FieldGenerator::generate($attributes, $relationMap, $allowedFields, $disabledFields, $modelFQCN, $allowedAggregations) : [],
            includes: ($features['includes'] ?? true) ? IncludeGenerator::generate($relationMap, $allowedIncludes, $disabledIncludes) : [],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>|Collection<int, array<string, mixed>>  $modelAttributes
     * @param  array<int, string>  $visible
     * @param  array<int, string>  $hidden
     * @return array<int, array<string, mixed>>
     */
    private static function filterAttributesByVisibleAndHidden(array|Collection $modelAttributes, array $visible, array $hidden): array
    {
        $attributes = [];
        foreach ($modelAttributes as $attribute) {
            /** @var array<string, mixed> $attribute */
            $name = $attribute['name'];

            if (! empty($visible) && ! in_array($name, $visible, true)) {
                continue;
            }

            $isHidden = $attribute['hidden'] ?? false;
            if (in_array($name, $hidden, true) || $isHidden === true) {
                continue;
            }

            $attributes[] = $attribute;
        }

        /** @var array<int, array<string, mixed>> */
        return $attributes;
    }

    /**
     * @param  array<string>  $configFields
     * @param  array<int, string>  $validFields
     */
    private static function validateConfigFields(string $modelFQCN, array $configFields, array $validFields, string $featureName): void
    {
        if (empty($configFields)) {
            return;
        }

        $invalid = array_diff($configFields, $validFields);
        if (! empty($invalid)) {
            throw new \LogicException(sprintf(
                'Configuration error in Model [%s]. You tried to configure %s for fields/relations that do not exist: %s',
                $modelFQCN,
                $featureName,
                implode(', ', $invalid)
            ));
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeModelInspectorAttributes(string $modelFQCN, ?string $connection = null): array
    {
        /** @var ModelInfo|array{attributes?: array<int, array<string, mixed>>|Collection<int, array<string, mixed>>} $modelInfo */
        $modelInfo = (new ModelInspector(app()))->inspect($modelFQCN, $connection);

        if (is_array($modelInfo)) {
            $modelAttributes = $modelInfo['attributes'] ?? [];

            if ($modelAttributes instanceof Collection) {
                /** @var array<int, array<string, mixed>> */
                return $modelAttributes->toArray();
            }

            /** @var array<int, array<string, mixed>> */
            return $modelAttributes;
        }

        /** @var array<int, array<string, mixed>> $attributes */
        $attributes = $modelInfo->attributes->toArray();

        return $attributes;
    }

    /**
     * @param  class-string<Model>  $modelFQCN
     * @return array<string, mixed>
     */
    public static function getQueryGuide(string $modelFQCN): array
    {
        $resource = self::generate($modelFQCN);

        return [
            'model' => class_basename($modelFQCN),
            'available_filters' => self::mapFiltersToArray($resource->filters),
            'available_sorts' => self::mapSortsToArray($resource->sorts),
            'available_fields' => array_keys($resource->fields),
            'available_includes' => self::mapIncludesToArray($resource->includes),
            'pagination_settings' => self::mapPaginationToArray($resource->pagination),
            'syntax' => [
                'filters' => '{"field":{"operator":"value"}}',
                'sorts' => '{"field":"direction"}',
                'fields' => '["field1","field2"]',
                'includes' => '["relation1","relation2"]',
                'page' => '{"number":1,"limit":10}',
            ],
        ];
    }

    /**
     * @param  class-string<Model>  $modelFQCN
     * @return array<string, mixed>
     */
    public static function getFilterSchema(string $modelFQCN): array
    {
        $resource = self::generate($modelFQCN);

        return [
            'model' => class_basename($modelFQCN),
            'filters' => self::cleanUpFilters($resource->filters),
            'sorts' => array_keys($resource->filters),
            'fields' => array_keys($resource->fields),
            'includes' => self::cleanUpIncludes($resource->includes),
            'pagination' => self::mapPaginationToArray($resource->pagination),
        ];
    }

    /** @param array<string, FilterConfig> $rawFilters
     * @return array<string, array{type: string, operations: array<string>}>
     */
    private static function cleanUpFilters(array $rawFilters): array
    {
        $filters = [];
        foreach ($rawFilters as $name => $data) {
            if ($data->type === 'relation_id') {
                if ($data->isAlias === false && Str::snake($name) !== $name) {
                    continue;
                }

                if ($data->isAlias === true) {
                    $fancyName = Str::snake($data->mapsTo ?? '');
                    if ($name !== $fancyName && isset($rawFilters[$fancyName])) {
                        continue;
                    }
                }
            }

            $filters[$name] = [
                'type' => $data->type instanceof AbstractType ? $data->type->value : $data->type,
                'operations' => $data->operations,
            ];
        }

        return $filters;
    }

    /** @param array<string, IncludeConfig> $rawIncludes
     * @return array<string, array{related: string, type: string}>
     */
    private static function cleanUpIncludes(array $rawIncludes): array
    {
        $includes = [];
        foreach ($rawIncludes as $name => $data) {
            if (Str::snake($name) !== $name) {
                continue;
            }

            $fancyName = Str::snake($data->mapsTo);
            if ($name !== $fancyName && isset($rawIncludes[$fancyName])) {
                continue;
            }

            $includes[$name] = [
                'related' => $data->related,
                'type' => $data->type,
            ];
        }

        return $includes;
    }

    /** @param array<string, FilterConfig> $filters
     * @return array<string, array{type: string, operations: array<string>, is_alias: bool, maps_to: string|null, is_scope: bool}>
     */
    private static function mapFiltersToArray(array $filters): array
    {
        $result = [];
        foreach ($filters as $name => $config) {
            $result[$name] = [
                'type' => $config->type instanceof AbstractType ? $config->type->value : $config->type,
                'operations' => $config->operations,
                'is_alias' => $config->isAlias,
                'maps_to' => $config->mapsTo,
                'is_scope' => $config->isScope,
            ];
        }

        return $result;
    }

    /** @param array<string, SortConfig> $sorts
     * @return array<string, array{operations: array<string>, is_alias: bool, maps_to: string|null}>
     */
    private static function mapSortsToArray(array $sorts): array
    {
        $result = [];
        foreach ($sorts as $name => $config) {
            $result[$name] = [
                'operations' => $config->operations,
                'is_alias' => $config->isAlias,
                'maps_to' => $config->mapsTo,
            ];
        }

        return $result;
    }

    /** @param array<string, IncludeConfig> $includes
     * @return array<string, array{type: string, related: string, is_alias: bool, maps_to: string}>
     */
    private static function mapIncludesToArray(array $includes): array
    {
        $result = [];
        foreach ($includes as $name => $config) {
            $result[$name] = [
                'type' => $config->type,
                'related' => $config->related,
                'is_alias' => $config->isAlias,
                'maps_to' => $config->mapsTo,
            ];
        }

        return $result;
    }

    /** @return array{keys: array<string>, defaults: array<string, mixed>} */
    private static function mapPaginationToArray(PaginationConfig $pagination): array
    {
        return [
            'keys' => $pagination->keys,
            'defaults' => $pagination->defaults,
        ];
    }
}
