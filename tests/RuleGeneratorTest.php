<?php

declare(strict_types=1);

use Victormgomes\LaravelQueryEngine\Support\Resource\FieldConfig;
use Victormgomes\LaravelQueryEngine\Support\Resource\FilterConfig;
use Victormgomes\LaravelQueryEngine\Support\Resource\PaginationConfig;
use Victormgomes\LaravelQueryEngine\Support\Resource\ResourceConfig;
use Victormgomes\LaravelQueryEngine\Support\Resource\SortConfig;
use Victormgomes\LaravelQueryEngine\Support\RuleGenerator;

it('generates basic rules when resources are empty', function (): void {
    $resources = new ResourceConfig(
        filters: [],
        sorts: [],
        pagination: new PaginationConfig(keys: [], defaults: []),
        fields: [],
        includes: [],
    );

    $rules = RuleGenerator::generate($resources);

    expect($rules)->toHaveKey('filters', ['sometimes', 'array'])
        ->and($rules)->toHaveKey('sorts', ['sometimes', 'array'])
        ->and($rules)->toHaveKey('fields', ['sometimes', 'array'])
        ->and($rules)->toHaveKey('includes', ['sometimes', 'array']);
});

it('generates specific array rules when resources have items', function (): void {
    $resources = new ResourceConfig(
        filters: ['name' => new FilterConfig(type: 'string', operations: ['eq'])],
        sorts: ['created_at' => new SortConfig(operations: ['asc', 'desc'])],
        pagination: new PaginationConfig(keys: [], defaults: []),
        fields: ['id' => new FieldConfig(operations: ['add']), 'name' => new FieldConfig(operations: ['add'])],
        includes: [],
    );

    $rules = RuleGenerator::generate($resources);

    expect($rules['filters'])->toBe(['sometimes', 'array:name'])
        ->and($rules['sorts'])->toBe(['sometimes', 'array:created_at'])
        ->and($rules['fields'])->toBe(['sometimes', 'array'])
        ->and($rules['includes'])->toBe(['sometimes', 'array']);
});
