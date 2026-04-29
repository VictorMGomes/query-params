<?php

declare(strict_types=1);

namespace Victormgomes\QueryParams\Support;

use Illuminate\Database\Eloquent\ModelInspector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Victormgomes\QueryParams\Enums\AssociatedIndex;
use Victormgomes\QueryParams\Enums\Operators;

class Resource
{
    public static function generate(string $modelFQCN, $connection = null): array
    {
        $inspector = new ModelInspector(app());
        $modelInstance = new $modelFQCN;
        $visible = $modelInstance->getVisible();
        $hidden = $modelInstance->getHidden();

        // Use configured metadata connection if none provided
        $connection ??= Config::get('query-params.metadata_connection');

        /** @var \Illuminate\Database\Eloquent\ModelInfo $modelInfo */
        $modelInfo = $inspector->inspect($modelFQCN, $connection);

        $attributes = [];
        foreach ($modelInfo->attributes as $attribute) {
            $name = $attribute['name'];

            // 1. If $visible is defined, only include fields in $visible
            if (! empty($visible) && ! in_array($name, $visible, true)) {
                continue;
            }

            // 2. Always respect $hidden
            if (in_array($name, $hidden, true) || ($attribute['hidden'] ?? false) === true) {
                continue;
            }

            $attributes[] = $attribute;
        }

        $relationMap = RelationMapper::getMap($modelFQCN);

        return [
            'filters' => self::generateFilters($attributes, $relationMap),
            'sorts' => self::generateSorts($attributes, $relationMap),
            'pagination' => self::generatePagination(),
            'fields' => self::generateFields($attributes),
            'includes' => self::generateIncludes($relationMap),
        ];
    }

    /**
     * Returns a rich metadata structure specifically designed for frontend dynamic filter builders.
     * Fully covers all 5 operations: Filters, Sorts, Fields, Includes, and Pagination.
     */
    public static function getMetadata(string $modelFQCN): array
    {
        $resource = self::generate($modelFQCN);

        return [
            'model' => class_basename($modelFQCN),
            'available_filters' => $resource['filters'],
            'available_sorts' => $resource['sorts'],
            'available_fields' => array_keys($resource['fields']),
            'available_includes' => $resource['includes'],
            'pagination_settings' => $resource['pagination'],
            'syntax' => [
                'filter' => 'field:operator:value',
                'sort' => 'field:direction',
                'fields' => 'field1,field2',
                'include' => 'relation1,relation2',
                'page' => 'number:X,limit:Y',
            ],
        ];
    }

    /**
     * Returns a definitive, cleaned-up metadata structure for frontend services.
     * Hides redundant aliases and internal mapping details.
     */
    public static function getDefinitiveMetadata(string $modelFQCN): array
    {
        $resource = self::generate($modelFQCN);

        // 1. Clean up Filters: Prioritize unique names, prefer snake_case for relations
        $filters = [];
        foreach ($resource['filters'] as $name => $data) {
            if ($data['type'] === 'relation_id') {
                // Skip if it is a camelCase method (we prefer the snake_case alias)
                if (isset($data['is_alias']) && $data['is_alias'] === false && Str::snake($name) !== $name) {
                    continue;
                }

                // If it is an alias, check if it's the FK name.
                // We keep the "fancy" name (like 'people') and skip the FK name (like 'people_id')
                // unless the fancy name itself doesn't exist.
                if (isset($data['is_alias']) && $data['is_alias'] === true) {
                    $fancyName = Str::snake($data['maps_to']);
                    if ($name !== $fancyName && isset($resource['filters'][$fancyName])) {
                        continue;
                    }
                }
            }

            $filters[$name] = [
                'type' => $data['type'],
                'operations' => $data['operations'],
            ];
        }

        // 2. Clean up Includes: Unique snake_case names only
        $includes = [];
        foreach ($resource['includes'] as $name => $data) {
            // Keep only the snake_case version of the relation
            if (Str::snake($name) !== $name) {
                continue;
            }

            // If this name maps to the same relation as another name already in the list, skip it if it's "less fancy"
            // For example, if we have 'people' and 'people_id' mapping to 'people', we keep 'people'.
            $fancyName = Str::snake($data['maps_to']);
            if ($name !== $fancyName && isset($resource['includes'][$fancyName])) {
                continue;
            }

            $includes[$name] = [
                'related' => $data['related'],
                'type' => $data['type'],
            ];
        }

        return [
            'model' => class_basename($modelFQCN),
            'filters' => $filters,
            'sorts' => array_keys($filters),
            'fields' => array_keys($resource['fields']),
            'includes' => $includes,
            'pagination' => $resource['pagination'],
        ];
    }

    private static function generateFilters(array|Collection $attributes, array $relationMap = []): array
    {
        $operators = Operators::toArray();
        $operatorTypes = Types::getOperatorTypes();

        $filters = [];

        foreach ($attributes as $attribute) {
            $columnType = Types::resolveType($attribute['type'] ?? 'string');
            $allowedOps = [];

            foreach ($operators as $operator) {
                $allowedTypes = $operatorTypes[$operator] ?? [];
                if (in_array($columnType, $allowedTypes, true)) {
                    $allowedOps[] = $operator;
                }
            }

            if (! empty($allowedOps)) {
                $filters[$attribute['name']] = [
                    'type' => $columnType,
                    'operations' => $allowedOps,
                ];
            }
        }

        // Add relations that can be filtered via Foreign Key
        foreach ($relationMap as $name => $data) {
            if (isset($data['foreign_key']) && ! isset($filters[$name])) {
                $filters[$name] = [
                    'type' => 'relation_id',
                    'operations' => [Operators::EQ, Operators::NE, Operators::IN, Operators::NIN],
                    'is_alias' => $data['is_alias'] ?? false,
                    'maps_to' => $data['foreign_key'],
                ];
            }
        }

        return $filters;
    }

    private static function generateSorts(array|Collection $attributes, array $relationMap = []): array
    {
        $sorts = [];
        foreach ($attributes as $attribute) {
            $sorts[$attribute['name']] = [
                'operations' => ['asc', 'desc'],
            ];
        }

        // Add relations that can be sorted via Foreign Key
        foreach ($relationMap as $name => $data) {
            if (isset($data['foreign_key']) && ! isset($sorts[$name])) {
                $sorts[$name] = [
                    'operations' => ['asc', 'desc'],
                    'is_alias' => $data['is_alias'] ?? false,
                    'maps_to' => $data['foreign_key'],
                ];
            }
        }

        return $sorts;
    }

    private static function generatePagination(): array
    {
        return [
            'keys' => [AssociatedIndex::NUMBER, AssociatedIndex::LIMIT],
            'defaults' => [
                'limit' => 10,
                'max_limit' => 100,
            ],
        ];
    }

    private static function generateFields(array|Collection $attributes): array
    {
        $fields = [];
        foreach ($attributes as $attribute) {
            $fields[$attribute['name']] = [
                'operations' => ['add'],
            ];
        }

        return $fields;
    }

    private static function generateIncludes(array $relationMap): array
    {
        $includes = [];
        foreach ($relationMap as $name => $data) {
            $includes[$name] = [
                'type' => $data['type'] ?? 'Relation',
                'related' => $data['related'] ?? '',
                'is_alias' => $data['is_alias'] ?? false,
                'maps_to' => $data['real_name'],
            ];
        }

        return $includes;
    }
}
