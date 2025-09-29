<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Facades;

use Illuminate\Validation\Rule;
use Victormgomes\Queryparams\Enums\RuleType;
use Victormgomes\Queryparams\Helpers\Types;

class Rules
{
    public static function generate(array $resources): array
    {
        $filtersRules = self::generateFilters($resources);
        $sortsRules = self::generateSorts($resources);
        $fieldsRules = self::generateFields($resources);
        $includesRules = self::generateIncludes($resources);
        $pagesRules = self::generatePages($resources);

        return array_merge(
            $filtersRules,
            $sortsRules,
            $fieldsRules,
            $includesRules,
            $pagesRules
        );
    }

    private static function generateFilters(array $resources): array
    {
        $rules = [];

        $operatorRules = Types::getOperatorRules();

        foreach ($resources['filters'] as $filter => $value) {
            foreach ($value['operations'] as $operator) {
                $rule_key = 'filters'.'{'.$filter.'}'.'{'."$operator".'}';
                $rule_value = $operatorRules[$operator];
                $rules[$rule_key] = $rule_value;
            }
        }

        return $rules;
    }

    private static function generateSorts(array $resources): array
    {
        $rules = [];

        foreach ($resources['sorts'] as $sort => $value) {
            $rule_key = 'sorts'.'{'.$sort.'}';
            $rule_value = [RuleType::SOMETIMES, Rule::in($value['operations'])];
            $rules[$rule_key] = $rule_value;
        }

        return $rules;
    }

    private static function generateFields(array $resources): array
    {
        $rules = [];

        foreach ($resources['fields'] as $field => $value) {
            $rule_key = 'fields'.'{'.$field.'}';
            $rule_value = ['string', 'sometimes', Rule::in($value['operations'])];
            $rules[$rule_key] = $rule_value;
        }

        return $rules;
    }

    private static function generateIncludes(array $resources): array
    {
        $rules = [];

        foreach ($resources['includes'] as $include => $value) {
            $rule_key = 'includes'.'{'.$include.'}';
            $rule_value = ['string', 'sometimes', Rule::in($value['operations'])];
            $rules[$rule_key] = $rule_value;
        }

        return $rules;
    }

    private static function generatePages(array $resources): array
    {
        $rules = [];

        foreach ($resources['pagination'] as $page => $value) {
            $rule_key = 'page'.'{'.$value.'}';
            $rule_value = 'sometimes|integer|min:1';
            if ($value === 'limit') {
                $rule_value = 'sometimes|integer|min:1|max:100';
            }
            $rules[$rule_key] = $rule_value;
        }

        return $rules;
    }
}
