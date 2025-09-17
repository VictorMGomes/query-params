<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers\Rules;

use Illuminate\Validation\Rule;

class Includes
{
    public static function generateRules(array $fields): array
    {

        $relations = [];

        foreach ($fields as $field => $value) {
            if ($value['type'] == 'relation') {
                $relations[] = $field;
            }
        }

        $rules = [];

        // Ensure "includes" is optional, but if present, it must be an array
        $rules['includes'] = ['sometimes', 'array'];

        // Each element of the array must be in $relations
        $rules['includes.*'] = ['string', Rule::in($relations)];

        return $rules;
    }
}
