<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers\Rules;

use Victormgomes\Queryparams\Enums\Operators;
use Victormgomes\Queryparams\Helpers\Types;

class Filters
{
    public static function generateRules(array $fields): array
    {
        $operators = Operators::toArray();           // e.g., ['eq', 'like', '>', '<']
        $operatorRules = Types::getOperatorRules();  // e.g., ['eq' => ['sometimes','string'], ...]
        $operatorTypes = Types::getOperatorTypes();  // allowed types per operator

        $rules = [];

        foreach ($fields as $field => $value) {
            if ($value['type'] == 'relation') {
                continue;
            }

            foreach ($operators as $operator) {
                $allowedTypes = $operatorTypes[$operator] ?? [];
                $fieldType = $value['type'] ?? 'string';

                $rule_key = "filters" . "{" . $field . "}" . "{" . "$operator" . "}";
                $rule_value = $operatorRules[$operator];

                if (in_array($fieldType, $allowedTypes, true)) {
                    $rules[$rule_key] = $rule_value;
                }
            }
        }

        return $rules;
    }
}
