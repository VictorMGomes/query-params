<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Builder\Operations\Types;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Victormgomes\LaravelQueryEngine\Enums\Operators;

class StringHandler implements FilterOperation
{
    /** @param EloquentBuilder<Model>|QueryBuilder $query */
    public function handle(EloquentBuilder|QueryBuilder $query, string $field, Operators $operator, mixed $value): void
    {
        $connection = $query->getConnection();
        $isPgsql = method_exists($connection, 'getDriverName') && $connection->getDriverName() === 'pgsql';

        $likeValue = match (true) {
            is_string($value) => $value,
            is_numeric($value) => (string) $value,
            default => '',
        };

        match ($operator) {
            Operators::LIKE => $query->where($field, 'like', "%{$likeValue}%"),
            Operators::NOTLIKE => $query->where($field, 'not like', "%{$likeValue}%"),
            Operators::ILIKE => $isPgsql ? $query->where($field, 'ilike', "%{$likeValue}%") : $query->where($field, 'like', "%{$likeValue}%"),
            Operators::NOTILIKE => $isPgsql ? $query->where($field, 'not ilike', "%{$likeValue}%") : $query->where($field, 'not like', "%{$likeValue}%"),
            Operators::FTS => $query->whereFullText($field, $likeValue),
            default => null,
        };
    }
}
