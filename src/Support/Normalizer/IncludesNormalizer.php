<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Normalizer;

use Victormgomes\LaravelQueryEngine\Support\RelationMapper;

class IncludesNormalizer
{
    /** @return array<int|string, string|array{fields: string[]}> */
    public static function normalize(mixed $includesRaw, ?string $modelFQCN): array
    {
        $includes = (array) $includesRaw;
        $parsed = [];

        foreach ($includes as $key => $value) {
            if (is_string($key)) {
                $relation = $modelFQCN
                    ? (RelationMapper::resolveRelation($modelFQCN, $key) ?? $key)
                    : $key;

                if (is_array($value)) {
                    if (array_is_list($value)) {
                        $value = ['fields' => $value];
                    }

                    $parsed[$relation] = [
                        'fields' => array_map(fn (mixed $v): string => is_scalar($v) ? (string) $v : '', (array) ($value['fields'] ?? [])),
                    ];
                } else {
                    $parsed[$relation] = ['fields' => []];
                }
            } else {
                $include = trim(is_scalar($value) ? (string) $value : '');

                if ($modelFQCN) {
                    $include = RelationMapper::resolveRelation($modelFQCN, $include) ?? $include;
                }

                $parsed[] = $include;
            }
        }

        /** @var array<int|string, string|array{fields: array<string>}> */
        return $parsed;
    }
}
