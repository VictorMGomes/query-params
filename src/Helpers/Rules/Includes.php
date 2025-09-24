<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers\Rules;

use Illuminate\Validation\Rule;

class Includes
{
    public static function generateRules(array $fields): array
    {

        $relations = [];
        $rules = [];

        foreach ($fields as $field => $value) {
            if ($value['type'] == 'relation') {
                $relations[$field] = $value;
            }
        }

        foreach ($relations as $field => $value) {
            $rule_key = "includes" . "{" . $field . "}";
            $rule_value = ['string', 'sometimes', Rule::in('add')];
            $rules[$rule_key] = $rule_value;
        };

        return $rules;
    }
}
