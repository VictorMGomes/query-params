<?php

declare(strict_types=1);

namespace Victormgomes\QueryParams\Maps;

use Victormgomes\QueryParams\Enums\AbstractType;
use Victormgomes\QueryParams\Enums\AssociatedIndex;
use Victormgomes\QueryParams\Enums\Operators;
use Victormgomes\QueryParams\Enums\RuleType;

class TypesMap
{
    public static function abstract(): array
    {
        $typeGroups = [
            AbstractType::INTEGER => [
                'integer',
                'bigint',
                'smallint',
                'mediumint',
                'tinyint',
                'int',
                'int4',
                'int8',
            ],
            AbstractType::NUMERIC => [
                'decimal',
                'float',
                'double',
                'numeric',
                'real',
            ],
            AbstractType::STRING => [
                'string',
                'text',
                'guid',
                'char',
                'varchar',
                'character varying',
            ],
            AbstractType::BOOLEAN => [
                'boolean',
                'bool',
            ],
            AbstractType::DATE => [
                'date',
            ],
            AbstractType::DATETIME => [
                'datetime',
                'datetimetz',
                'timestamp',
                'timestamptz',
                'timestamp without time zone',
                'timestamp with time zone',
            ],
            AbstractType::ARRAY => [
                'json',
                'array',
                'simple_array',
                'jsonb',
            ],
        ];

        $flattened = [];
        foreach ($typeGroups as $abstractType => $dbTypes) {
            foreach ($dbTypes as $dbType) {
                $flattened[$dbType] = $abstractType;
            }
        }

        return $flattened;
    }

    public static function operator(): array
    {
        $allTypes = [
            AbstractType::STRING,
            AbstractType::INTEGER,
            AbstractType::NUMERIC,
            AbstractType::BOOLEAN,
            AbstractType::DATE,
            AbstractType::DATETIME,
        ];

        $numericTypes = [
            AbstractType::INTEGER,
            AbstractType::NUMERIC,
            AbstractType::DATE,
            AbstractType::DATETIME,
        ];

        return [
            Operators::EQ => [
                AssociatedIndex::TYPES => $allTypes,
                AssociatedIndex::RULES => RuleType::build(RuleType::SOMETIMES),
            ],
            Operators::NE => [
                AssociatedIndex::TYPES => $allTypes,
                AssociatedIndex::RULES => RuleType::build(RuleType::SOMETIMES),
            ],
            Operators::GT => [
                AssociatedIndex::TYPES => $numericTypes,
                AssociatedIndex::RULES => RuleType::build(RuleType::SOMETIMES),
            ],
            Operators::GTE => [
                AssociatedIndex::TYPES => $numericTypes,
                AssociatedIndex::RULES => RuleType::build(RuleType::SOMETIMES),
            ],
            Operators::LT => [
                AssociatedIndex::TYPES => $numericTypes,
                AssociatedIndex::RULES => RuleType::build(RuleType::SOMETIMES),
            ],
            Operators::LTE => [
                AssociatedIndex::TYPES => $numericTypes,
                AssociatedIndex::RULES => RuleType::build(RuleType::SOMETIMES),
            ],
            Operators::IN => [
                AssociatedIndex::TYPES => $allTypes,
                AssociatedIndex::RULES => RuleType::build(RuleType::ARRAY, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::NIN => [
                AssociatedIndex::TYPES => $allTypes,
                AssociatedIndex::RULES => RuleType::build(RuleType::ARRAY, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::NULL => [
                AssociatedIndex::TYPES => $allTypes,
                AssociatedIndex::RULES => RuleType::build(RuleType::BOOLEAN, RuleType::SOMETIMES),
            ],
            Operators::NOTNULL => [
                AssociatedIndex::TYPES => $allTypes,
                AssociatedIndex::RULES => RuleType::build(RuleType::BOOLEAN, RuleType::SOMETIMES),
            ],
            Operators::BETWEEN => [
                AssociatedIndex::TYPES => $numericTypes,
                AssociatedIndex::RULES => RuleType::build(RuleType::ARRAY, RuleType::SIZE_2, RuleType::SOMETIMES),
            ],
            Operators::NBETWEEN => [
                AssociatedIndex::TYPES => $numericTypes,
                AssociatedIndex::RULES => RuleType::build(RuleType::ARRAY, RuleType::SIZE_2, RuleType::SOMETIMES),
            ],
            Operators::LIKE => [
                AssociatedIndex::TYPES => [AbstractType::STRING],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::NOTLIKE => [
                AssociatedIndex::TYPES => [AbstractType::STRING],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::ILIKE => [
                AssociatedIndex::TYPES => [AbstractType::STRING],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::NOTILIKE => [
                AssociatedIndex::TYPES => [AbstractType::STRING],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::CONTAINS => [
                AssociatedIndex::TYPES => [AbstractType::ARRAY, AbstractType::STRING],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::CONTAINEDBY => [
                AssociatedIndex::TYPES => [AbstractType::ARRAY, AbstractType::STRING],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::FTS => [
                AssociatedIndex::TYPES => [AbstractType::STRING],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
        ];
    }
}
