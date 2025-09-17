<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers;

use Illuminate\Support\Facades\Log;

class MetaData
{
    public static function getRules(object $instance): array
    {
        $start = microtime(true);

        $modelName = Model::getModel($instance);

        $modelSummary = ModelInspector::fieldsSummary($modelName);

        $rules = Rules::generate($modelSummary);

        $end = microtime(true);

        $duration = $end - $start;

        Log::info("Rules generation for {$modelName} took {$duration} seconds.");

        return $rules;
    }
}
