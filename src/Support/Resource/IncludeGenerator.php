<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Resource;

use Victormgomes\LaravelQueryEngine\Support\RelationInfo;

final class IncludeGenerator
{
    /**
     * @param  array<string, RelationInfo>  $relationMap
     * @param  array<string>|null  $allowedIncludes
     * @param  array<string>  $disabledIncludes
     * @return array<string, IncludeConfig>
     */
    public static function generate(array $relationMap, ?array $allowedIncludes = null, array $disabledIncludes = []): array
    {
        $includes = [];
        foreach ($relationMap as $name => $data) {
            if ($allowedIncludes !== null && ! in_array($name, $allowedIncludes, true)) {
                continue;
            }
            if (in_array($name, $disabledIncludes, true)) {
                continue;
            }

            $includes[$name] = new IncludeConfig(
                type: $data->type,
                related: $data->related,
                isAlias: $data->isAlias,
                mapsTo: $data->realName,
            );
        }

        return $includes;
    }
}
