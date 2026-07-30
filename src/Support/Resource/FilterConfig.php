<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Resource;

use Victormgomes\LaravelQueryEngine\Enums\AbstractType;

final class FilterConfig
{
    /** @param array<string> $operations */
    public function __construct(
        public readonly AbstractType|string $type,
        /** @var array<string> */
        public readonly array $operations,
        public readonly bool $isAlias = false,
        public readonly ?string $mapsTo = null,
        public readonly bool $isScope = false,
    ) {}
}
