<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support\Normalizer;

use Illuminate\Support\Facades\Config;
use Victormgomes\LaravelQueryEngine\Enums\AssociatedIndex;

class FeaturesNormalizer
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function filter(array $data): array
    {
        /** @var array{includes?: bool, sorts?: bool, fields?: bool, filters?: bool, page?: bool} $features */
        $features = Config::get('laravel-query-engine.features', [
            'filters' => true,
            'sorts' => true,
            'includes' => true,
            'fields' => true,
            'page' => true,
        ]);

        if (! ($features['includes'] ?? true)) {
            unset($data[AssociatedIndex::INCLUDES->value]);
        }
        if (! ($features['sorts'] ?? true)) {
            unset($data[AssociatedIndex::SORTS->value]);
        }
        if (! ($features['fields'] ?? true)) {
            unset($data[AssociatedIndex::FIELDS->value]);
        }
        if (! ($features['filters'] ?? true)) {
            unset($data[AssociatedIndex::FILTERS->value]);
        }
        if (! ($features['page'] ?? true)) {
            unset($data[AssociatedIndex::PAGE->value]);
        }

        return $data;
    }
}
