<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers;

use Victormgomes\Queryparams\Enums\AbstractType;
use Victormgomes\Queryparams\Enums\AssociatedIndex;
use Victormgomes\Queryparams\Maps\TypesMap;

class Types
{
    public static function getOperatorTypes(): array
    {
        return array_map(fn($config) => $config[AssociatedIndex::TYPES], TypesMap::operator());
    }

    public static function getOperatorRules(): array
    {
        return array_map(fn($config) => $config[AssociatedIndex::RULES], TypesMap::operator());
    }

    public static function resolveType(string $databaseType): string
    {
        $map = TypesMap::abstract();

        return $map[$databaseType] ?? AbstractType::STRING;
    }

    public static function getColumnTypes(array $table): array
    {

        $columns = $table[AssociatedIndex::COLUMNS];

        $columnsTypes = [];

        foreach ($columns as $column) {
            $databaseType = $column[AssociatedIndex::TYPE];
            $abstractType = Types::resolveType($databaseType);
            $columnsTypes[$column[AssociatedIndex::NAME]] = $abstractType;
        }

        return $columnsTypes;
    }
}
