<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers;

use Victormgomes\Queryparams\Helpers\Rules\Fields;
use Victormgomes\Queryparams\Helpers\Rules\Filters;
use Victormgomes\Queryparams\Helpers\Rules\Includes;
use Victormgomes\Queryparams\Helpers\Rules\Pages;
use Victormgomes\Queryparams\Helpers\Rules\Sorts;

class QueryResources
{
    public static function generate(array $table): array
    {
        $filterRules = Filters::generateRules($table);
        $sortRules = Sorts::generateRules($table);
        $pageRules = Pages::generateRules();
        $fieldRules = Fields::generateRules($table);
        $includeRules = Includes::generateRules($table);

        return array_merge(
            $filterRules,
            $sortRules,
            $pageRules,
            $fieldRules,
            $includeRules
        );
    }
}
