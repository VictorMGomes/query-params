<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Maps;

use Victormgomes\Queryparams\Enums\AbstractType;
use Victormgomes\Queryparams\Enums\AssociatedIndex;
use Victormgomes\Queryparams\Enums\DatabaseType;
use Victormgomes\Queryparams\Enums\Operators;
use Victormgomes\Queryparams\Enums\RuleType;

class TypesMap
{
    public static function abstract(): array
    {
        $typeGroups = [
            AbstractType::INTEGER => [
                DatabaseType::INTEGER,
                DatabaseType::BIGINT,
                DatabaseType::SMALLINT,
                DatabaseType::MEDIUMINT,
                DatabaseType::TINYINT,
            ],
            AbstractType::NUMERIC => [
                DatabaseType::DECIMAL,
                DatabaseType::FLOAT,
                DatabaseType::DOUBLE,
            ],
            AbstractType::STRING => [
                DatabaseType::STRING,
                DatabaseType::TEXT,
                DatabaseType::GUID,
                DatabaseType::CHAR,
            ],
            AbstractType::BOOLEAN => [
                DatabaseType::BOOLEAN,
            ],
            AbstractType::DATE => [
                DatabaseType::DATETIME,
                DatabaseType::DATETIMETZ,
                DatabaseType::DATE,
                DatabaseType::TIMESTAMP,
            ],
            AbstractType::ARRAY => [
                DatabaseType::JSON,
                DatabaseType::ARRAY,
                DatabaseType::SIMPLE_ARRAY,
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
        $operatorConfig = [
            Operators::EQ => [
                AssociatedIndex::TYPES => [
                    AbstractType::STRING,
                    AbstractType::INTEGER,
                    AbstractType::NUMERIC,
                    AbstractType::BOOLEAN,
                    AbstractType::DATE,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::NE => [
                AssociatedIndex::TYPES => [
                    AbstractType::STRING,
                    AbstractType::INTEGER,
                    AbstractType::NUMERIC,
                    AbstractType::BOOLEAN,
                    AbstractType::DATE,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::GT => [
                AssociatedIndex::TYPES => [
                    AbstractType::NUMERIC,
                    AbstractType::DATE,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::NUMERIC, RuleType::SOMETIMES),
            ],
            Operators::GTE => [
                AssociatedIndex::TYPES => [
                    AbstractType::NUMERIC,
                    AbstractType::DATE,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::NUMERIC, RuleType::SOMETIMES),
            ],
            Operators::LT => [
                AssociatedIndex::TYPES => [
                    AbstractType::NUMERIC,
                    AbstractType::DATE,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::NUMERIC, RuleType::SOMETIMES),
            ],
            Operators::LTE => [
                AssociatedIndex::TYPES => [
                    AbstractType::NUMERIC,
                    AbstractType::DATE,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::NUMERIC, RuleType::SOMETIMES),
            ],
            Operators::IN => [
                AssociatedIndex::TYPES => [
                    AbstractType::STRING,
                    AbstractType::INTEGER,
                    AbstractType::NUMERIC,
                    AbstractType::DATE,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::ARRAY, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::NIN => [
                AssociatedIndex::TYPES => [
                    AbstractType::STRING,
                    AbstractType::INTEGER,
                    AbstractType::NUMERIC,
                    AbstractType::DATE,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::ARRAY, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::NULL => [
                AssociatedIndex::TYPES => [
                    AbstractType::STRING,
                    AbstractType::INTEGER,
                    AbstractType::NUMERIC,
                    AbstractType::BOOLEAN,
                    AbstractType::DATE,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::BOOLEAN, RuleType::SOMETIMES),
            ],
            Operators::NOTNULL => [
                AssociatedIndex::TYPES => [
                    AbstractType::STRING,
                    AbstractType::INTEGER,
                    AbstractType::NUMERIC,
                    AbstractType::BOOLEAN,
                    AbstractType::DATE,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::BOOLEAN, RuleType::SOMETIMES),
            ],
            Operators::BETWEEN => [
                AssociatedIndex::TYPES => [
                    AbstractType::NUMERIC,
                    AbstractType::DATE,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::ARRAY, RuleType::SIZE_2, RuleType::SOMETIMES),
            ],
            Operators::NBETWEEN => [
                AssociatedIndex::TYPES => [
                    AbstractType::NUMERIC,
                    AbstractType::DATE,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::ARRAY, RuleType::SIZE_2, RuleType::SOMETIMES),
            ],
            Operators::LIKE => [
                AssociatedIndex::TYPES => [
                    AbstractType::STRING,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::NOTLIKE => [
                AssociatedIndex::TYPES => [
                    AbstractType::STRING,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::ILIKE => [
                AssociatedIndex::TYPES => [
                    AbstractType::STRING,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::NOTILIKE => [
                AssociatedIndex::TYPES => [
                    AbstractType::STRING,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::OR => [
                AssociatedIndex::TYPES => [
                    AbstractType::ARRAY,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::ARRAY, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::AND => [
                AssociatedIndex::TYPES => [
                    AbstractType::ARRAY,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::ARRAY, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::NOT => [
                AssociatedIndex::TYPES => [
                    AbstractType::ARRAY,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::ARRAY, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::EXISTS => [
                AssociatedIndex::TYPES => [
                    AbstractType::ARRAY,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::BOOLEAN, RuleType::SOMETIMES),
            ],
            Operators::NOTEXISTS => [
                AssociatedIndex::TYPES => [
                    AbstractType::ARRAY,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::BOOLEAN, RuleType::SOMETIMES),
            ],
            Operators::CONTAINS => [
                AssociatedIndex::TYPES => [
                    AbstractType::ARRAY,
                    AbstractType::STRING,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::CONTAINEDBY => [
                AssociatedIndex::TYPES => [
                    AbstractType::ARRAY,
                    AbstractType::STRING,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::OVERLAP => [
                AssociatedIndex::TYPES => [
                    AbstractType::ARRAY,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::ARRAY, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
            Operators::FTS => [
                AssociatedIndex::TYPES => [
                    AbstractType::STRING,
                ],
                AssociatedIndex::RULES => RuleType::build(RuleType::STRING, RuleType::MIN_1, RuleType::SOMETIMES),
            ],
        ];

        return $operatorConfig;
    }
}
