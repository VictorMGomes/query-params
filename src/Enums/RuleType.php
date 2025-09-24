<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Enums;

use ReflectionClass;

final class RuleType
{
    public const STRING = 'string';

    public const NUMERIC = 'numeric';

    public const BOOLEAN = 'boolean';

    public const ARRAY = 'array';

    public const SOMETIMES = 'sometimes';

    public const SIZE_2 = 'size:2';

    public const REQUIRED = 'required';

    public const FILLED = 'filled';

    public const MIN_1 = 'min:1';

    public static function build(string ...$rules): string
    {
        return implode('|', $rules);
    }

    public static function toArray(): array
    {
        return array_values((new ReflectionClass(self::class))->getConstants());
    }
}
