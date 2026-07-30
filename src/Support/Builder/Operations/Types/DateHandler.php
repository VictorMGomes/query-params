<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Builder\Operations\Types;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Victormgomes\LaravelQueryEngine\Enums\Operators;

class DateHandler implements FilterOperation
{
    /** @param EloquentBuilder<Model>|QueryBuilder $query */
    public function handle(EloquentBuilder|QueryBuilder $query, string $field, Operators $operator, mixed $value): void
    {
        $intValue = is_numeric($value) ? (int) $value : 0;
        $stringValue = match (true) {
            is_string($value) => $value,
            is_numeric($value) => (string) $value,
            default => '',
        };

        match ($operator) {
            Operators::YEAR => $query->whereYear($field, $intValue),
            Operators::MONTH => $query->whereMonth($field, $intValue),
            Operators::DAY => $query->whereDay($field, $intValue),
            Operators::DATE => $query->whereDate($field, $stringValue),
            Operators::TIME => $query->whereTime($field, '=', $stringValue),
            default => null,
        };
    }
}
