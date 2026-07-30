<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** @template TModel of Model */
interface FieldResolver
{
    /** @param Builder<TModel> $query */
    public function applyFilter(Builder $query, string $field, string $operator, mixed $value, string $locale): bool;

    /** @param Builder<TModel> $query */
    public function applySort(Builder $query, string $field, string $direction, string $locale): bool;

    public function translateItem(Model $item, string $locale): mixed;
}
