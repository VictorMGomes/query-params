<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Victormgomes\LaravelQueryEngine\Enums\AbstractType;
use Victormgomes\LaravelQueryEngine\Enums\RuleType;
use Victormgomes\LaravelQueryEngine\Support\Resource\FilterConfig;
use Victormgomes\LaravelQueryEngine\Support\Resource\ResourceConfig;

final class RuleGenerator
{
    /** @return array<string, mixed> */
    public static function generate(ResourceConfig $resources): array
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

    /** @return array<string, mixed> */
    private static function generateFilters(ResourceConfig $resources): array
    {
        $rules = [];
        $allowedFields = array_keys($resources->filters);

        $rules['filters'] = ['sometimes', 'array'.($allowedFields !== [] ? ':'.implode(',', $allowedFields) : '')];

        if ($allowedFields === []) {
            return $rules;
        }

        $operatorRules = Types::getOperatorRules();

        foreach ($resources->filters as $field => $config) {
            self::generateFilterRulesForField($rules, $field, $config, $operatorRules);
        }

        return $rules;
    }

    /** @param array<string, mixed> $rules
     * @param  array<string, string>  $operatorRules
     */
    private static function generateFilterRulesForField(array &$rules, string $field, FilterConfig $config, array $operatorRules): void
    {
        $allowedOps = $config->operations;
        $rules['filters.'.$field] = ['sometimes', 'array:'.implode(',', $allowedOps)];

        $dbType = $config->type;
        $dbTypeValue = $dbType instanceof AbstractType ? $dbType->value : (string) $dbType;

        foreach ($allowedOps as $operator) {
            $baseRule = $operatorRules[$operator];
            $finalRule = RuleType::build($dbTypeValue, $baseRule);
            $rules['filters.'.$field.'.'.$operator] = $finalRule;

            if (str_contains($finalRule, RuleType::ARRAY)) {
                $rules['filters.'.$field.'.'.$operator.'.*'] = RuleType::build($dbTypeValue, RuleType::SOMETIMES);
            }
        }
    }

    /** @return array<string, mixed> */
    private static function generateSorts(ResourceConfig $resources): array
    {
        $rules = [];
        $allowedFields = array_keys($resources->sorts);

        $rules['sorts'] = ['sometimes', 'array'.($allowedFields !== [] ? ':'.implode(',', $allowedFields) : '')];

        if ($allowedFields === []) {
            return $rules;
        }

        foreach ($resources->sorts as $field => $config) {
            $rules['sorts.'.$field] = [RuleType::SOMETIMES, Rule::in($config->operations)];
        }

        return $rules;
    }

    /** @return array<string, mixed> */
    private static function generateFields(ResourceConfig $resources): array
    {
        $rules = [];
        $allowedFields = array_keys($resources->fields);

        $rules['fields'] = ['sometimes', 'array'];

        if ($allowedFields === []) {
            return $rules;
        }

        $rules['fields.*'] = ['string', Rule::in($allowedFields)];

        return $rules;
    }

    /** @return array<string, mixed> */
    private static function generateIncludes(ResourceConfig $resources): array
    {
        $rules = [];
        $allowedIncludes = array_keys($resources->includes);

        $rules['includes'] = ['sometimes', 'array'];

        if ($allowedIncludes === []) {
            return $rules;
        }

        $rules['includes.*'] = ['string', Rule::in($allowedIncludes)];

        return $rules;
    }

    /** @return array<string, mixed> */
    private static function generatePages(ResourceConfig $resources): array
    {
        $rules = [];
        $allowedPages = $resources->pagination->keys;

        if ($allowedPages === []) {
            return $rules;
        }

        $rules['page'] = ['sometimes', 'array:'.implode(',', $allowedPages)];

        foreach ($allowedPages as $page) {
            $rules['page.'.$page] = self::getPageRule($page);
        }

        return $rules;
    }

    /** @return array<int, string> */
    private static function getPageRule(string $page): array
    {
        if ($page === 'limit') {
            $maxLimit = Config::get('laravel-query-engine.pagination.max_limit', 100);

            return ['sometimes', 'integer', 'min:1', 'max:'.(is_scalar($maxLimit) ? (string) $maxLimit : '100')];
        }

        if ($page === 'cursor') {
            return ['sometimes', 'string'];
        }

        return ['sometimes', 'integer', 'min:1'];
    }
}
