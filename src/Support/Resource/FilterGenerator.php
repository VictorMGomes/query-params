<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Resource;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use ReflectionClass;
use Victormgomes\LaravelQueryEngine\Enums\AbstractType;
use Victormgomes\LaravelQueryEngine\Enums\Operators;
use Victormgomes\LaravelQueryEngine\Support\RelationInfo;
use Victormgomes\LaravelQueryEngine\Support\Types;

final class FilterGenerator
{
    /** @var array<string, FilterConfig> */
    private array $filters = [];

    /** @var array<string, array<AbstractType>> */
    private array $operatorTypes;

    /**
     * @param  array<int, array<string, mixed>>|Collection<int, array<string, mixed>>  $attributes
     * @param  array<string, RelationInfo>  $relationMap
     * @param  array<string>|null  $allowedFilters
     * @param  array<string>  $disabledFilters
     * @param  array<string>  $allowedOperators
     * @param  array<string>  $allowedScopes
     */
    public function __construct(
        /** @var array<int, array<string, mixed>>|Collection<int, array<string, mixed>> $attributes */
        private readonly array|Collection $attributes,
        /** @var array<string, RelationInfo> */
        private readonly array $relationMap,
        private readonly ?string $modelFQCN,
        /** @var array<string>|null */
        private readonly ?array $allowedFilters,
        /** @var array<string> */
        private readonly array $disabledFilters,
        /** @var array<string> */
        private readonly array $allowedOperators,
        /** @var array<string> */
        private readonly array $allowedScopes
    ) {
        $this->operatorTypes = Types::getOperatorTypes();
    }

    /**
     * @param  array<int, array<string, mixed>>|Collection<int, array<string, mixed>>  $attributes
     * @param  array<string, RelationInfo>  $relationMap
     * @param  array<string>|null  $allowedFilters
     * @param  array<string>  $disabledFilters
     * @param  array<string>|null  $modelAllowedOperators
     * @param  array<string>|null  $modelDisableOperators
     * @param  array<string>  $allowedScopes
     * @return array<string, FilterConfig>
     */
    public static function generate(
        array|Collection $attributes,
        array $relationMap = [],
        ?string $modelFQCN = null,
        ?array $allowedFilters = null,
        array $disabledFilters = [],
        ?array $modelAllowedOperators = null,
        ?array $modelDisableOperators = null,
        array $allowedScopes = []
    ): array {
        /** @var list<string> $allowedOperators */
        $allowedOperators = (array) ($modelAllowedOperators ?? Config::get('laravel-query-engine.allowed_operators', Operators::values()));
        if (! empty($modelDisableOperators)) {
            $allowedOperators = array_values(array_diff($allowedOperators, $modelDisableOperators));
        }
        /** @var list<string> $operators */
        $operators = array_intersect(Operators::values(), $allowedOperators);

        $generator = new self(
            $attributes,
            $relationMap,
            $modelFQCN,
            $allowedFilters,
            $disabledFilters,
            $operators,
            $allowedScopes
        );

        return $generator->build();
    }

    /** @return array<string, FilterConfig> */
    private function build(): array
    {
        $this->generateStandardFilters();
        $this->appendRelationFilters();
        $this->appendExistenceFilters();
        $this->appendSoftDeletesFilters();
        $this->appendScopeFilters();

        return $this->filters;
    }

    private function isFilterAllowed(string $name): bool
    {
        if ($this->allowedFilters !== null && ! in_array($name, $this->allowedFilters, true)) {
            return false;
        }

        return ! in_array($name, $this->disabledFilters, true);
    }

    private function generateStandardFilters(): void
    {
        foreach ($this->attributes as $attribute) {
            /** @var array<string, mixed> $attribute */
            $name = is_scalar($attribute['name']) ? (string) $attribute['name'] : '';
            if (! $this->isFilterAllowed($name)) {
                continue;
            }

            $rawType = $attribute['type'] ?? 'string';
            $columnType = Types::resolveType(is_scalar($rawType) ? (string) $rawType : 'string');
            $allowedOps = $this->getOperationsForType($columnType);

            if (! empty($allowedOps)) {
                $this->filters[$name] = new FilterConfig(
                    type: $columnType,
                    operations: $allowedOps,
                );
            }
        }
    }

    /** @return list<string> */
    private function getOperationsForType(AbstractType $columnType): array
    {
        return array_values(array_filter(
            $this->allowedOperators,
            fn (string $op): bool => in_array($columnType, $this->operatorTypes[$op] ?? [], true)
        ));
    }

    private function appendRelationFilters(): void
    {
        $relationOps = array_intersect(
            [Operators::EQ->value, Operators::NE->value, Operators::IN->value, Operators::NIN->value],
            $this->allowedOperators
        );

        if (empty($relationOps)) {
            return;
        }

        foreach ($this->relationMap as $name => $data) {
            if (! $this->isFilterAllowed($name) || $data->foreignKey === null || isset($this->filters[$name])) {
                continue;
            }

            $this->filters[$name] = new FilterConfig(
                type: 'relation_id',
                operations: array_values($relationOps),
                isAlias: $data->isAlias,
                mapsTo: $data->foreignKey,
            );
        }
    }

    private function appendExistenceFilters(): void
    {
        $relationOps = array_intersect([Operators::EXISTS->value, Operators::NOTEXISTS->value], $this->allowedOperators);

        if (empty($relationOps)) {
            return;
        }

        $ops = array_values($relationOps);

        foreach ($this->relationMap as $name => $data) {
            if (! $this->isFilterAllowed($name)) {
                continue;
            }

            if (isset($this->filters[$name])) {
                $existing = $this->filters[$name];
                $mergedOps = array_values(array_unique(array_merge(
                    $existing->operations,
                    $ops
                )));
                $this->filters[$name] = new FilterConfig(
                    type: $existing->type,
                    operations: $mergedOps,
                    isAlias: $existing->isAlias,
                    mapsTo: $existing->mapsTo,
                    isScope: $existing->isScope,
                );

                continue;
            }

            $this->filters[$name] = new FilterConfig(
                type: 'relation',
                operations: $ops,
                isAlias: $data->isAlias,
                mapsTo: $data->realName,
            );
        }
    }

    private function appendSoftDeletesFilters(): void
    {
        if (! $this->modelFQCN || ! in_array(SoftDeletes::class, class_uses_recursive($this->modelFQCN), true)) {
            return;
        }

        $booleanOps = array_intersect([Operators::EQ->value], $this->allowedOperators);
        if (! empty($booleanOps)) {
            $ops = array_values($booleanOps);
            $this->filters['with_deleted'] = new FilterConfig(type: 'boolean', operations: $ops);
            $this->filters['only_deleted'] = new FilterConfig(type: 'boolean', operations: $ops);
        }
    }

    private function appendScopeFilters(): void
    {
        if (empty($this->allowedScopes) || ! $this->modelFQCN) {
            return;
        }

        /** @var class-string<Model> $modelFQCN */
        $modelFQCN = $this->modelFQCN;
        $reflection = new ReflectionClass($modelFQCN);
        foreach ($this->allowedScopes as $scope) {
            $methodName = 'scope'.ucfirst($scope);
            if (! $reflection->hasMethod($methodName)) {
                continue;
            }

            $hasParams = $reflection->getMethod($methodName)->getNumberOfParameters() > 1;

            $this->filters[$scope] = new FilterConfig(
                type: $hasParams ? 'string' : 'boolean',
                operations: [Operators::EQ->value],
                isScope: true,
            );
        }
    }
}
