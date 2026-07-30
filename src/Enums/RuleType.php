<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Enums;

final class RuleType
{
    public const STRING = 'string';

    public const INTEGER = 'integer';

    public const NUMERIC = 'numeric';

    public const BOOLEAN = 'boolean';

    public const DATE = 'date';

    public const ARRAY = 'array';

    public const MIN_1 = 'min:1';

    public const SIZE_2 = 'size:2';

    public const SOMETIMES = 'sometimes';

    /** @param string|array<int, string> ...$parts */
    public static function build(string|array ...$parts): string
    {
        /** @var array<int, string> $rules */
        $rules = [];

        foreach ($parts as $part) {
            if (is_string($part)) {
                $rules = array_merge($rules, explode('|', $part));
            } elseif (is_array($part)) {
                $rules = array_merge($rules, $part);
            }
        }

        $rules = array_unique($rules);

        return self::removeStringTypeWhenMoreSpecificTypeExists($rules);
    }

    /** @param array<int, string> $rules */
    private static function removeStringTypeWhenMoreSpecificTypeExists(array $rules): string
    {
        $hasSpecificType = count(array_intersect($rules, [self::INTEGER, self::NUMERIC, self::BOOLEAN, self::DATE, self::ARRAY])) > 0;

        if ($hasSpecificType) {
            $rules = array_diff($rules, [self::STRING]);
        }

        return implode('|', array_filter($rules));
    }
}
