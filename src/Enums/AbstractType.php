<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Enums;

use ReflectionClass;

final class AbstractType
{
    public const STRING = 'string';

    public const INTEGER = 'integer';

    public const NUMERIC = 'numeric';

    public const BOOLEAN = 'boolean';

    public const DATE = 'date';

    public const ARRAY = 'array';

    public static function toArray(): array
    {
        return array_values((new ReflectionClass(self::class))->getConstants());
    }
}
