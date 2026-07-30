<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Normalizer;

use Victormgomes\LaravelQueryEngine\Support\RelationMapper;

class SortsNormalizer
{
    /** @return array<string, string> */
    public static function normalize(mixed $sortsRaw, ?string $modelFQCN): array
    {
        $sorts = (array) $sortsRaw;

        if ($modelFQCN) {
            $mappedSorts = [];

            foreach ($sorts as $field => $dir) {
                $mappedSorts[RelationMapper::resolveFilterField($modelFQCN, $field)] = $dir;
            }

            $sorts = $mappedSorts;
        }

        /** @var array<string, string> */
        return $sorts;
    }
}
