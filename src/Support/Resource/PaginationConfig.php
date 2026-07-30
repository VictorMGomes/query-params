<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Resource;

final class PaginationConfig
{
    /** @param array<string> $keys
     * @param  array<string, mixed>  $defaults
     */
    public function __construct(
        /** @var array<string> */
        public readonly array $keys,
        /** @var array<string, mixed> */
        public readonly array $defaults,
    ) {}
}
