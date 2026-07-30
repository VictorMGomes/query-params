<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Resource;

final class SortConfig
{
    /** @param array<string> $operations */
    public function __construct(
        /** @var array<string> */
        public readonly array $operations,
        public readonly bool $isAlias = false,
        public readonly ?string $mapsTo = null,
    ) {}
}
