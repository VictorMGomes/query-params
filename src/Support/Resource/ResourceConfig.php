<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Resource;

final class ResourceConfig
{
    /** @param array<string, FilterConfig> $filters
     * @param  array<string, SortConfig>  $sorts
     * @param  array<string, FieldConfig>  $fields
     * @param  array<string, IncludeConfig>  $includes
     */
    public function __construct(
        /** @var array<string, FilterConfig> */
        public readonly array $filters,
        /** @var array<string, SortConfig> */
        public readonly array $sorts,
        public readonly PaginationConfig $pagination,
        /** @var array<string, FieldConfig> */
        public readonly array $fields,
        /** @var array<string, IncludeConfig> */
        public readonly array $includes,
    ) {}
}
