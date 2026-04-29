<?php

declare(strict_types=1);

namespace Victormgomes\QueryParams\Support;

use Illuminate\Database\Eloquent\ModelInspector;
use Illuminate\Support\Collection;
use Victormgomes\QueryParams\Enums\AssociatedIndex;
use Victormgomes\QueryParams\Enums\Operators;

class Resource
{
    public static function generate(string $modelFQCN, $connection = null): array
    {
        $inspector = new ModelInspector(app());
        /** @var \Illuminate\Database\Eloquent\ModelInfo $modelInfo */
        $modelInfo = $inspector->inspect($modelFQCN, $connection);

        $attributes = [];
        foreach ($modelInfo->attributes as $attribute) {
            if (($attribute['hidden'] ?? false) === true) {
                continue;
            }
            $attributes[] = $attribute;
        }

        return [
            'filters' => self::generateFilters($attributes),
            'sorts' => self::generateSorts($attributes),
            'pagination' => self::generatePagination(),
            'fields' => self::generateFields($attributes),
            'includes' => self::generateIncludes($modelInfo->relations),
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

    private static function generateFilters(array|Collection $attributes): array
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

        return $filters;
    }

    private static function generateSorts(array|Collection $attributes): array
    {
        $sorts = [];
        foreach ($attributes as $attribute) {
            $sorts[$attribute['name']] = [
                'operations' => ['asc', 'desc'],
            ];
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

    private static function generateIncludes(array|Collection $relations): array
    {
        $includes = [];
        foreach ($relations as $relation) {
            $includes[$relation['name']] = [
                'type' => $relation['type'] ?? 'Relation',
                'related' => class_basename($relation['related'] ?? ''),
            ];
        }

        return $includes;
    }
}
