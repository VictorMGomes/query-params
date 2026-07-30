<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Resource;

final class FieldConfig
{
    /** @param array<string> $operations */
    public function __construct(
        /** @var array<string> */
        public readonly array $operations,
        public readonly bool $isAccessor = false,
        public readonly bool $isAggregation = false,
        public readonly ?string $aggType = null,
        public readonly ?string $relation = null,
        public readonly ?string $column = null,
    ) {}
}
