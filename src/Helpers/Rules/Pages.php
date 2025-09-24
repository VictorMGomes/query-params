<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers\Rules;

use Victormgomes\Queryparams\Enums\AssociatedIndex;

class Pages
{
    /**
     * @return array<string, string>
     */
    public static function generateRules(): array
    {
        $rules = [];

        $rules[AssociatedIndex::PAGE . "{" . AssociatedIndex::NUMBER . "}"] = 'sometimes|integer|min:1';
        $rules[AssociatedIndex::PAGE . "{" . AssociatedIndex::LIMIT . "}"] = 'sometimes|integer|min:1|max:100';

        return $rules;
    }
}
