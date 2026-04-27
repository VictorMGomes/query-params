<?php

declare(strict_types=1);

namespace Victormgomes\QueryParams;

use Illuminate\Support\Facades\Log;
use Victormgomes\QueryParams\Facades\Resource;
use Victormgomes\QueryParams\Facades\Rules as FacadesRules;

class Rules
{
    public static function generate(string $modelFQCN): array
    {
        $start = microtime(true);

        $resources = Resource::generate($modelFQCN);

        $rules = FacadesRules::generate($resources);

        $end = microtime(true);

        $duration = $end - $start;

        Log::info("Rules generation for {$modelFQCN} took {$duration} seconds.");

        return $rules;
    }
}
