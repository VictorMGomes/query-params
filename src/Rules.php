<?php

declare(strict_types=1);

namespace Victormgomes\QueryParams;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Victormgomes\QueryParams\Support\Resource;
use Victormgomes\QueryParams\Support\RuleGenerator;

class Rules
{
    public static function generate(string $modelFQCN): array
    {
        $rules = self::getRules($modelFQCN);

        if (Config::get('query-params.debug', false)) {
            Log::info("Generated rules for {$modelFQCN}: ".json_encode($rules));
        }

        return $rules;
    }

    private static function getRules(string $modelFQCN): array
    {
        $enabled = Config::get('query-params.caching.enabled', true);
        $force = Config::get('query-params.force_cache', false);
        $isProduction = Config::get('app.env') === 'production';

        if (! ($enabled && ($isProduction || $force))) {
            return self::buildRules($modelFQCN);
        }

        $cacheKey = 'rules.'.md5($modelFQCN);
        $ttl = Config::get('query-params.caching.ttl', 3600);

        // Try using tags for easier clearing if supported
        $cache = Cache::getFacadeRoot();
        if ($cache->supportsTags()) {
            return $cache->tags(['query-params'])->remember($cacheKey, $ttl, fn () => self::buildRules($modelFQCN));
        }

        return $cache->remember('query-params.'.$cacheKey, $ttl, fn () => self::buildRules($modelFQCN));
    }

    private static function buildRules(string $modelFQCN): array
    {
        $resources = Resource::generate($modelFQCN);

        return RuleGenerator::generate($resources);
    }
}
