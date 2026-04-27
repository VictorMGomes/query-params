<?php

declare(strict_types=1);

namespace Victormgomes\QueryParams\Helpers;

use Victormgomes\QueryParams\Helpers\Rules\Fields;
use Victormgomes\QueryParams\Helpers\Rules\Filters;
use Victormgomes\QueryParams\Helpers\Rules\Includes;
use Victormgomes\QueryParams\Helpers\Rules\Pages;
use Victormgomes\QueryParams\Helpers\Rules\Sorts;

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
