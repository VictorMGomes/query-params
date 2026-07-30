<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Builder\Operations\Types;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Victormgomes\LaravelQueryEngine\Enums\Operators;

interface FilterOperation
{
    /** @param EloquentBuilder<Model>|QueryBuilder $query */
    public function handle(EloquentBuilder|QueryBuilder $query, string $field, Operators $operator, mixed $value): void;
}
