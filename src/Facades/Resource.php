<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Facades;

use Illuminate\Database\Eloquent\ModelInspector;
use Illuminate\Support\Collection;
use Victormgomes\Queryparams\Enums\AssociatedIndex;
use Victormgomes\Queryparams\Enums\Operators;
use Victormgomes\Queryparams\Helpers\Types;

class Resource
{
    public static function generate(string $modelFQCN, $connection = null): array
    {
        $inspect = new ModelInspector(app());

        $table = $inspect->inspect($modelFQCN, $connection);

        $attributes = [];
        foreach ($table['attributes'] as $attribute) {
            if (empty($attribute) === true or $attribute['hidden'] === true or $attribute['appended'] === true) {
                continue;
            }
            array_push($attributes, $attribute);
        }

        return [
            'filters' => self::generateFilters($attributes),
            'sorts' => self::generateSorts($attributes),
            'pagination' => self::generatePagination(),
            'fields' => self::generateFields($attributes),
            'includes' => self::generateIncludes($table['relations']),
        ];
    }

    private static function generateFilters(array|Collection $attributes): array
    {
        $operators = Operators::toArray();           // e.g., ['eq', 'like', '>', '<']
        $operatorTypes = Types::getOperatorTypes();  // allowed types per operator

        $filters = [];

        foreach ($attributes as $attribute) {
            // $fieldType = $attribute['cast'] ?? $attribute['type'];
            $fieldType = 'string';
            $allowedOps = [];

            foreach ($operators as $operator) {
                $allowedTypes = $operatorTypes[$operator] ?? [];

                if (in_array($fieldType, $allowedTypes, true)) {
                    $allowedOps[] = $operator;
                }
            }

            if (! empty($allowedOps)) {
                $filters[$attribute['name']] = [
                    'type' => $fieldType,
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
        $pagination = [
            AssociatedIndex::NUMBER,
            AssociatedIndex::LIMIT,
        ];

        return $pagination;
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
                'operations' => ['add'],
            ];
        }

        return $includes;
    }
}
