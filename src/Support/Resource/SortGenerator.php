<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Resource;

use Illuminate\Support\Collection;
use Victormgomes\LaravelQueryEngine\Support\RelationInfo;

final class SortGenerator
{
    /** @var array<string, SortConfig> */
    private array $sorts = [];

    /**
     * @param  array<int, array<string, mixed>>|Collection<int, array<string, mixed>>  $attributes
     * @param  array<string, RelationInfo>  $relationMap
     * @param  array<string>|null  $allowedSorts
     * @param  array<string>  $disabledSorts
     */
    public function __construct(
        /** @var array<int, array<string, mixed>>|Collection<int, array<string, mixed>> $attributes */
        private readonly array|Collection $attributes,
        /** @var array<string, RelationInfo> */
        private readonly array $relationMap,
        /** @var array<string>|null */
        private readonly ?array $allowedSorts,
        /** @var array<string> */
        private readonly array $disabledSorts
    ) {}

    /**
     * @param  array<int, array<string, mixed>>|Collection<int, array<string, mixed>>  $attributes
     * @param  array<string, RelationInfo>  $relationMap
     * @param  array<string>|null  $allowedSorts
     * @param  array<string>  $disabledSorts
     * @return array<string, SortConfig>
     */
    public static function generate(
        array|Collection $attributes,
        array $relationMap = [],
        ?array $allowedSorts = null,
        array $disabledSorts = []
    ): array {
        $generator = new self(
            $attributes,
            $relationMap,
            $allowedSorts,
            $disabledSorts
        );

        return $generator->build();
    }

    /** @return array<string, SortConfig> */
    private function build(): array
    {
        $this->generateStandardSorts();
        $this->appendRelationSorts();

        return $this->sorts;
    }

    private function isSortAllowed(string $name): bool
    {
        if ($this->allowedSorts !== null && ! in_array($name, $this->allowedSorts, true)) {
            return false;
        }

        return ! in_array($name, $this->disabledSorts, true);
    }

    private function generateStandardSorts(): void
    {
        foreach ($this->attributes as $attribute) {
            /** @var array<string, mixed> $attribute */
            $name = is_scalar($attribute['name']) ? (string) $attribute['name'] : '';
            if ($this->isSortAllowed($name)) {
                $this->sorts[$name] = new SortConfig(
                    operations: ['asc', 'desc'],
                );
            }
        }
    }

    private function appendRelationSorts(): void
    {
        foreach ($this->relationMap as $name => $data) {
            if (! $this->isSortAllowed($name)) {
                continue;
            }

            if ($data->foreignKey !== null && ! isset($this->sorts[$name])) {
                $this->sorts[$name] = new SortConfig(
                    operations: ['asc', 'desc'],
                    isAlias: $data->isAlias,
                    mapsTo: $data->foreignKey,
                );
            }
        }
    }
}
