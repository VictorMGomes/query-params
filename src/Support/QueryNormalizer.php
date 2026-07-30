<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Victormgomes\LaravelQueryEngine\Contracts\FieldResolver;
use Victormgomes\LaravelQueryEngine\Enums\AssociatedIndex;
use Victormgomes\LaravelQueryEngine\Support\Normalizer\FeaturesNormalizer;
use Victormgomes\LaravelQueryEngine\Support\Normalizer\FiltersNormalizer;
use Victormgomes\LaravelQueryEngine\Support\Normalizer\IncludesNormalizer;
use Victormgomes\LaravelQueryEngine\Support\Normalizer\SortsNormalizer;

class QueryNormalizer
{
    /** @var \WeakMap<object, bool> */
    protected static \WeakMap $normalized;

    public static function normalize(FormRequest|Request $request, ?string $modelFQCN = null): void
    {
        self::$normalized ??= new \WeakMap;

        if (isset(self::$normalized[$request])) {
            return;
        }

        /** @var array<string, mixed> $inputData */
        $inputData = $request->all();
        $data = self::decodeJsonValues($inputData);

        $data[AssociatedIndex::INCLUDES->value] = IncludesNormalizer::normalize($data[AssociatedIndex::INCLUDES->value] ?? [], $modelFQCN);
        $data[AssociatedIndex::SORTS->value] = SortsNormalizer::normalize($data[AssociatedIndex::SORTS->value] ?? [], $modelFQCN);
        $data[AssociatedIndex::FIELDS->value] = (array) ($data[AssociatedIndex::FIELDS->value] ?? []);
        $data[AssociatedIndex::FILTERS->value] = FiltersNormalizer::normalize($data[AssociatedIndex::FILTERS->value] ?? [], $modelFQCN);
        $data[AssociatedIndex::PAGE->value] = (array) ($data[AssociatedIndex::PAGE->value] ?? []);

        /** @var array<string, mixed> $data */
        $data = FeaturesNormalizer::filter($data);

        $request->query->replace($data);
        $request->replace($data);

        self::$normalized[$request] = true;
    }

    /** @return FieldResolver<Model>|null */
    public static function resolveDriver(): ?FieldResolver
    {
        $driverClass = Config::get('laravel-query-engine.drivers.default');

        return is_string($driverClass) && is_subclass_of($driverClass, FieldResolver::class) ? new $driverClass : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function decodeJsonValues(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && isset($value[0]) && in_array($value[0], ['{', '['], true)) {
                $decoded = json_decode($value, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $data[$key] = $decoded;
                }
            }
        }

        return $data;
    }
}
