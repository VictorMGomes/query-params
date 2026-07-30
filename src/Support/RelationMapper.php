<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use Throwable;

class RelationMapper
{
    /** @var array<string, array<string, RelationInfo>> */
    protected static array $cache = [];

    /** @return array<string, RelationInfo> */
    public static function getMap(Model|string $model): array
    {
        $class = is_string($model) ? $model : get_class($model);

        if (isset(self::$cache[$class])) {
            return self::$cache[$class];
        }

        /** @var Model $instance */
        $instance = is_string($model) ? new $model : $model;
        $reflection = new ReflectionClass($instance);
        $map = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (self::skipMethodWithRequiredParameters($method)) {
                continue;
            }

            if (self::isCoreLaravelMethod($method)) {
                continue;
            }

            if (self::inferRelationFromReturnType($method) === false) {
                continue;
            }

            if (self::isBlacklistedNonRelationMethod($method)) {
                continue;
            }

            $relationInfo = self::executeAndConfirmRelation($instance, $method);
            if ($relationInfo === null) {
                continue;
            }

            $realName = $method->getName();
            $snakeName = Str::snake($realName);

            $map[$realName] = $relationInfo;

            if ($snakeName !== $realName) {
                $map[$snakeName] = $relationInfo->withAlias();
            }

            if ($relationInfo->foreignKey && $relationInfo->foreignKey !== $realName && $relationInfo->foreignKey !== $snakeName) {
                $map[$relationInfo->foreignKey] = $relationInfo->withAlias(isFk: true);
            }
        }

        return self::$cache[$class] = $map;
    }

    public static function resolveRelation(Model|string $model, string $name): ?string
    {
        $map = self::getMap($model);

        return $map[$name]->realName ?? null;
    }

    public static function resolveFilterField(Model|string $model, string $name): string
    {
        $map = self::getMap($model);

        if (isset($map[$name]) && $map[$name]->foreignKey) {
            return $map[$name]->foreignKey;
        }

        return $name;
    }

    private static function skipMethodWithRequiredParameters(ReflectionMethod $method): bool
    {
        return $method->getNumberOfRequiredParameters() > 0;
    }

    private static function isCoreLaravelMethod(ReflectionMethod $method): bool
    {
        $declaringClass = $method->getDeclaringClass()->getName();

        return str_starts_with($declaringClass, 'Illuminate\Database\Eloquent') ||
            str_starts_with($declaringClass, 'Illuminate\Support\Traits');
    }

    private static function inferRelationFromReturnType(ReflectionMethod $method): ?bool
    {
        $returnType = $method->getReturnType();

        if (! $returnType) {
            return null;
        }

        $types = [];
        if ($returnType instanceof ReflectionNamedType) {
            $types[] = $returnType->getName();
        } elseif ($returnType instanceof ReflectionUnionType) {
            foreach ($returnType->getTypes() as $type) {
                if ($type instanceof ReflectionNamedType) {
                    $types[] = $type->getName();
                }
            }
        }

        $isRelation = false;
        $isExplicitlyNotRelation = false;

        foreach ($types as $typeName) {
            if (is_a($typeName, Relation::class, true)) {
                $isRelation = true;
                break;
            }

            if (in_array($typeName, ['bool', 'void', 'int', 'string', 'array', 'object', 'float', 'mixed', 'self', 'static', 'parent'])) {
                $isExplicitlyNotRelation = true;
            }
        }

        if ($isExplicitlyNotRelation && ! $isRelation) {
            return false;
        }

        return null;
    }

    private static function isBlacklistedNonRelationMethod(ReflectionMethod $method): bool
    {
        return in_array($method->getName(), [
            'jsonSerialize', 'toArray', 'replicate', 'push', 'save',
            'delete', 'forceDelete', 'restore', 'touch', 'refresh',
            'getAttributes', 'getOriginal', 'getDirty', 'getChanges',
            'wasChanged', 'isDirty', 'isClean', 'getRelations',
        ]);
    }

    private static function executeAndConfirmRelation(Model $instance, ReflectionMethod $method): ?RelationInfo
    {
        try {
            $return = @$instance->{$method->getName()}();

            if (! $return instanceof Relation) {
                return null;
            }

            return RelationInfo::fromRelation($return, $method->getName());
        } catch (Throwable $e) {
            return null;
        }
    }
}
