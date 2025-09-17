<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Enums;

use ReflectionClass;

final class Operators
{
    public const EQ = 'eq';        // Equal

    public const NE = 'ne';        // Not equal

    public const GT = 'gt';        // Greater than

    public const GTE = 'gte';      // Greater than or equal

    public const LT = 'lt';        // Less than

    public const LTE = 'lte';      // Less than or equal

    public const IN = 'in';        // In list

    public const NIN = 'nin';      // Not in list

    public const NULL = 'null';        // IS NULL

    public const NOTNULL = 'notnull';  // IS NOT NULL

    public const BETWEEN = 'between';     // BETWEEN a AND b

    public const NBETWEEN = 'nbetween';   // NOT BETWEEN a AND b

    public const LIKE = 'like';           // LIKE (case-sensitive)

    public const NOTLIKE = 'notlike';     // NOT LIKE

    public const ILIKE = 'ilike';         // ILIKE (case-insensitive)

    public const NOTILIKE = 'notilike';   // NOT ILIKE

    public const OR = 'or';       // Logical OR (for grouped filters)

    public const AND = 'and';     // Logical AND (optional explicit group)

    public const NOT = 'not';     // Negation (for filters or groups)

    public const EXISTS = 'exists';        // EXISTS subquery (logical presence)

    public const NOTEXISTS = 'notexists';  // NOT EXISTS

    public const CONTAINS = 'contains';        // field contains value

    public const CONTAINEDBY = 'containedby';  // field is contained by value

    public const OVERLAP = 'overlap';          // field overlaps with value

    public const FTS = 'fts';  // Full-text search

    public static function toArray(): array
    {
        return array_values((new ReflectionClass(self::class))->getConstants());
    }

    public static function tryFrom(string $value): ?string
    {
        $values = self::toArray();

        return in_array($value, $values, true) ? $value : null;
    }
}
