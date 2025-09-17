<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers\Rules;

use Illuminate\Validation\Rule;
use Victormgomes\Queryparams\Enums\RuleType;

class Groups
{
    public static function generateRules(array $fields): array
    {

        $allFields = [];

        foreach ($fields as $field => $value) {
            if ($value['type'] == 'relation') {
                continue;
            }
            $allFields[$field] = $value;
        }

        $rules['groups'] = ['sometimes', 'array'];

        $rules['groups.*'] = [RuleType::SOMETIMES, Rule::in(array_keys($allFields))];

        return $rules;
    }
}
