<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers\Rules;

use Illuminate\Validation\Rule;
use Victormgomes\Queryparams\Enums\RuleType;

class Sorts
{
    public static function generateRules(array $fields): array
    {
        $rules = [];
        foreach ($fields as $field => $value) {
            if ($value['type'] == 'relation') {
                continue;
            }

            $rule_key = "sorts" . "{" . $field . "}";
            $rule_value = [RuleType::SOMETIMES, Rule::in(['asc', 'desc'])];
            $rules[$rule_key] = $rule_value;
        }

        return $rules;
    }
}
