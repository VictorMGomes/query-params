<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Enums;

use ReflectionClass;

final class DatabaseType
{
    public const INTEGER = 'integer';

    public const BIGINT = 'bigint';

    public const SMALLINT = 'smallint';

    public const MEDIUMINT = 'mediumint';

    public const TINYINT = 'tinyint';

    public const DECIMAL = 'decimal';

    public const FLOAT = 'float';

    public const DOUBLE = 'double';

    public const STRING = 'string';

    public const TEXT = 'text';

    public const GUID = 'guid';

    public const CHAR = 'char';

    public const BOOLEAN = 'boolean';

    public const DATETIME = 'datetime';

    public const DATETIMETZ = 'datetimetz';

    public const DATE = 'date';

    public const TIMESTAMP = 'timestamp';

    public const JSON = 'json';

    public const ARRAY = 'array';

    public const SIMPLE_ARRAY = 'simple_array';

    public static function toArray(): array
    {
        return array_values((new ReflectionClass(self::class))->getConstants());
    }
}
