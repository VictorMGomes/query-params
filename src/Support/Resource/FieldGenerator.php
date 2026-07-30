<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Resource;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Victormgomes\LaravelQueryEngine\Support\RelationInfo;

class FieldGenerator
{
    /**
     * @param  array<int, array<string, mixed>>|Collection<int, array<string, mixed>>  $attributes
     * @param  array<string, RelationInfo>  $relationMap
     * @param  array<string>|null  $allowedFields
     * @param  array<string>  $disabledFields
     * @param  array<string>  $allowedAggregations
     * @return array<string, FieldConfig>
     */
    public static function generate(array|Collection $attributes, array $relationMap = [], ?array $allowedFields = null, array $disabledFields = [], ?string $modelFQCN = null, array $allowedAggregations = []): array
    {
        $fields = self::generateStandardFields($attributes, $allowedFields, $disabledFields);

        if ($modelFQCN) {
            $fields = self::appendAccessorFields($fields, $modelFQCN, $allowedFields, $disabledFields);
        }

        $fields = self::appendAggregationFields($fields, $relationMap, $allowedAggregations, $allowedFields, $disabledFields);

        return $fields;
    }

    /**
     * @param  array<int, array<string, mixed>>|Collection<int, array<string, mixed>>  $attributes
     * @param  array<string>|null  $allowedFields
     * @param  array<string>  $disabledFields
     * @return array<string, FieldConfig>
     */
    private static function generateStandardFields(array|Collection $attributes, ?array $allowedFields, array $disabledFields): array
    {
        $fields = [];
        foreach ($attributes as $attribute) {
            /** @var array<string, mixed> $attribute */
            $name = (string) (is_scalar($attribute['name']) ? $attribute['name'] : '');
            if ($allowedFields !== null && ! in_array($name, $allowedFields, true)) {
                continue;
            }
            if (in_array($name, $disabledFields, true)) {
                continue;
            }

            $fields[$name] = new FieldConfig(
                operations: ['add'],
            );
        }

        /** @var array<string, FieldConfig> */
        return $fields;
    }

    /**
     * @param  array<string, FieldConfig>  $fields
     * @param  array<string>|null  $allowedFields
     * @param  array<string>  $disabledFields
     * @return array<string, FieldConfig>
     */
    private static function appendAccessorFields(array $fields, string $modelFQCN, ?array $allowedFields, array $disabledFields): array
    {
        $accessors = self::getAccessors($modelFQCN);
        foreach ($accessors as $accessor) {
            if ($allowedFields !== null && ! in_array($accessor, $allowedFields, true)) {
                continue;
            }
            if (in_array($accessor, $disabledFields, true)) {
                continue;
            }

            $fields[$accessor] = new FieldConfig(
                operations: ['add'],
                isAccessor: true,
            );
        }

        return $fields;
    }

    /**
     * @param  array<string, FieldConfig>  $fields
     * @param  array<string, RelationInfo>  $relationMap
     * @param  array<string>  $allowedAggregations
     * @param  array<string>|null  $allowedFields
     * @param  array<string>  $disabledFields
     * @return array<string, FieldConfig>
     */
    private static function appendAggregationFields(array $fields, array $relationMap, array $allowedAggregations, ?array $allowedFields, array $disabledFields): array
    {
        foreach ($allowedAggregations as $agg) {
            if ($allowedFields !== null && ! in_array($agg, $allowedFields, true)) {
                continue;
            }
            if (in_array($agg, $disabledFields, true)) {
                continue;
            }

            if (preg_match('/^(.+)_(count|exists)$/', $agg, $matches)) {
                $relation = $matches[1];
                if (isset($relationMap[$relation])) {
                    $fields[$agg] = new FieldConfig(
                        operations: ['add'],
                        isAggregation: true,
                        aggType: $matches[2],
                        relation: $relation,
                    );
                }
            } elseif (preg_match('/^(.+)_(sum|avg|min|max)_(.+)$/', $agg, $matches)) {
                $relation = $matches[1];
                if (isset($relationMap[$relation])) {
                    $fields[$agg] = new FieldConfig(
                        operations: ['add'],
                        isAggregation: true,
                        aggType: $matches[2],
                        relation: $relation,
                        column: $matches[3],
                    );
                }
            }
        }

        return $fields;
    }

    /** @return array<int, string> */
    /** @return list<string> */
    /** @return list<string> */
    private static function getAccessors(string $modelFQCN): array
    {
        /** @var class-string<Model> $modelFQCN */
        /** @var ReflectionClass<Model> $reflection */
        $reflection = new ReflectionClass($modelFQCN);
        $accessors = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED) as $method) {
            $name = $method->getName();

            $accessor = self::detectLegacyAccessor($name)
                ?? self::detectModernAttributeAccessor($method);

            if ($accessor) {
                $accessors[] = $accessor;
            }
        }

        /** @var Model $instance */
        $instance = new $modelFQCN;
        /** @var array<int, string> $appends */
        $appends = $instance->getAppends();

        return array_values(array_unique(array_merge($accessors, $appends)));
    }

    private static function detectLegacyAccessor(string $methodName): ?string
    {
        if (str_starts_with($methodName, 'get') && str_ends_with($methodName, 'Attribute') && $methodName !== 'getAttribute') {
            return Str::snake(substr($methodName, 3, -9));
        }

        return null;
    }

    private static function detectModernAttributeAccessor(ReflectionMethod $method): ?string
    {
        $returnType = $method->getReturnType();
        if ($returnType instanceof ReflectionNamedType && $returnType->getName() === Attribute::class) {
            return Str::snake($method->getName());
        }

        return null;
    }
}
