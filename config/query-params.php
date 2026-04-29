<?php

declare(strict_types=1);
use App\Support\QueryDrivers\TranslationDriver;

return [
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
    | Pluggable Drivers
    |--------------------------------------------------------------------------
    |
    | Define custom resolvers for specific field behaviors like translations.
    |
    */
    'drivers' => [
        'translatable' => TranslationDriver::class,
    ],
];
