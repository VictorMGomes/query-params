<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams;

use Illuminate\Support\Facades\Log;
use Victormgomes\Queryparams\Helpers\ModelInspector;
use Victormgomes\Queryparams\Helpers\Rules as HelpersRules;

class Rules
{
    public static function generate(string $class): array
    {
        $start = microtime(true);

        $modelSummary = ModelInspector::fieldsSummary($class);

        $rules = HelpersRules::generate($modelSummary);

        $end = microtime(true);

        $duration = $end - $start;

        Log::info("Rules generation for {$class} took {$duration} seconds.");

        return $rules;
    }
}
