<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Builder\Operations\Types;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Victormgomes\LaravelQueryEngine\Enums\Operators;

class PostgresHandler implements FilterOperation
{
    /** @param EloquentBuilder<Model>|QueryBuilder $query */
    public function handle(EloquentBuilder|QueryBuilder $query, string $field, Operators $operator, mixed $value): void
    {
        if ($operator === Operators::CONTAINS) {
            $query->whereJsonContains($field, $value);

            return;
        }

        $this->assertPostgresConnection($query, $operator);

        // @codeCoverageIgnoreStart
        $grammar = $query instanceof EloquentBuilder ? $query->getQuery()->getGrammar() : $query->getGrammar();
        $wrappedField = $grammar->wrap($field);

        match ($operator) {
            // @phpstan-ignore-next-line runtime SQL string, not literal-string
            Operators::CONTAINEDBY => $query->whereRaw("? <@ {$wrappedField}", [$value]),
            // @phpstan-ignore-next-line runtime SQL string, not literal-string
            Operators::OVERLAP => $query->whereRaw("? && {$wrappedField}", [$value]),
            default => null,
        };
        // @codeCoverageIgnoreEnd
    }

    /** @param EloquentBuilder<Model>|QueryBuilder $query */
    private function assertPostgresConnection(EloquentBuilder|QueryBuilder $query, Operators $operator): void
    {
        $connection = $query->getConnection();
        if (! method_exists($connection, 'getDriverName') || $connection->getDriverName() !== 'pgsql') {
            throw new \InvalidArgumentException("The '{$operator->value}' operator is only supported on PostgreSQL databases.");
        }
    }
}
