<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers\Rules;

use Illuminate\Validation\Rule;

class Fields
{
    public static function generateRules(array $fields): array
    {
        $allFields = [];
        $rules = [];

        foreach ($fields as $field => $value) {
            if ($value['type'] == 'relation') {
                continue;
            }
            $allFields[$field] = $value;
        }


        foreach ($allFields as $field => $value) {
            $rule_key = "fields" . "{" . $field . "}";
            $rule_value = ['string', 'sometimes', Rule::in('add')];
            $rules[$rule_key] = $rule_value;
        };

        return $rules;
    }
}
