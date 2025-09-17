<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers\Rules;

use Illuminate\Validation\Rule;

class Fields
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

        $rules = [
            'fields' => [
                'sometimes',
                'array',
            ],
            'fields.*' => [
                'string',
                Rule::in(array_keys($allFields)),
            ],
        ];

        return $rules;
    }
}
