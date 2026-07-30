<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Resource;

final class IncludeConfig
{
    public function __construct(
        public readonly string $type,
        public readonly string $related,
        public readonly bool $isAlias = false,
        public readonly string $mapsTo = '',
    ) {}
}
