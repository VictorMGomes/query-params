<?php

declare(strict_types=1);

namespace Victormgomes\QueryParams;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Victormgomes\QueryParams\Support\Resource;
use Victormgomes\QueryParams\Support\RuleGenerator;

class Rules
{
    public static function generate(string $modelFQCN): array
    {
        $enabled = Config::get('query-params.caching.enabled', true);
        $force = Config::get('query-params.force_cache', false);
        $isProduction = Config::get('app.env') === 'production';

        if (! ($enabled && ($isProduction || $force))) {
            return self::buildRules($modelFQCN);
        }

        $cacheKey = 'query-params.rules.'.md5($modelFQCN);
        $ttl = Config::get('query-params.caching.ttl', 3600);

        return Cache::remember($cacheKey, $ttl, fn () => self::buildRules($modelFQCN));
    }

    private static function buildRules(string $modelFQCN): array
    {
        $resources = Resource::generate($modelFQCN);

        return RuleGenerator::generate($resources);
    }
}
