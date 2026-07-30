<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Builder\Operations\Types;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Victormgomes\LaravelQueryEngine\Enums\Operators;

class RelationHandler implements FilterOperation
{
    /** @param EloquentBuilder<Model>|QueryBuilder $query */
    public function handle(EloquentBuilder|QueryBuilder $query, string $field, Operators $operator, mixed $value): void
    {
        if (! $query instanceof EloquentBuilder) {
            return;
        }

        $isExists = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $hasCallback = is_array($value)
            && is_string($value['relation'] ?? null)
            && ($value['callback'] ?? null) instanceof \Closure;
        $relationName = $hasCallback ? $value['relation'] : '';
        $callback = $hasCallback ? $value['callback'] : null;

        match ($operator) {
            Operators::EXISTS => $hasCallback
                ? $query->whereHas($relationName, $callback)
                : ($isExists ? $query->has($field) : $query->doesntHave($field)),
            Operators::NOTEXISTS => $hasCallback
                ? $query->whereDoesntHave($relationName, $callback)
                : ($isExists ? $query->doesntHave($field) : $query->has($field)),
            default => null,
        };
    }
}
