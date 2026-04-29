<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Metadata Connection
    |--------------------------------------------------------------------------
    |
    | This is the database connection that will be used to inspect model
    | attributes and generate filters/sorts/etc.
    |
    */
    'metadata_connection' => env('QUERY_PARAMS_METADATA_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    */
    'caching' => [
        'enabled' => env('QUERY_PARAMS_CACHE_ENABLED', true),
        'ttl' => env('QUERY_PARAMS_CACHE_TTL', 3600),
    ],

    'force_cache' => env('QUERY_PARAMS_FORCE_CACHE', false),

    /*
    |--------------------------------------------------------------------------
    | Debug Configuration
    |--------------------------------------------------------------------------
    |
    | When enabled, the package will log the generated rules for each
    | form request that uses MapQueryParams.
    |
    */
    'debug' => env('QUERY_PARAMS_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Pluggable Drivers
    |--------------------------------------------------------------------------
    |
    | Define custom resolvers for specific field behaviors
    |
    */
    'drivers' => [
        // 'translatable' => App\Support\QueryDrivers\TranslationDriver::class,
    ],
];
