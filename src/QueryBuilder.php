<?php

declare(strict_types=1);

namespace Victormgomes\LaravelQueryEngine;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Validation\ValidationException;
use Victormgomes\LaravelQueryEngine\Contracts\FieldResolver;
use Victormgomes\LaravelQueryEngine\Enums\AbstractType;
use Victormgomes\LaravelQueryEngine\Enums\AssociatedIndex;
use Victormgomes\LaravelQueryEngine\Enums\Operators;
use Victormgomes\LaravelQueryEngine\Support\Builder\Operations\Filter;
use Victormgomes\LaravelQueryEngine\Support\ClassLoader;
use Victormgomes\LaravelQueryEngine\Support\QueryNormalizer;
use Victormgomes\LaravelQueryEngine\Support\Resource;

class QueryBuilder
{
    use Macroable;

    /**
     * @param  Builder<Model>|class-string<Model>  $queryOrModel
     * @return Builder<Model>
     */
    public static function buildQuery(EloquentBuilder|string $queryOrModel, FormRequest|Request $request): EloquentBuilder
    {
        if (is_string($queryOrModel)) {
            $model = ClassLoader::instantiateModel($queryOrModel);
            $query = $model->newQuery();
            $modelFQCN = $queryOrModel;
        } else {
            $query = $queryOrModel;
            $modelFQCN = get_class($query->getModel());
        }

        QueryNormalizer::normalize($request, $modelFQCN);
        /** @var array<string, mixed> $validated */
        $validated = $request instanceof FormRequest ? $request->validated() : $request->all();
        $validated = self::castDataTypes($validated, $modelFQCN);
        $locale = app()->getLocale();
        $driver = QueryNormalizer::resolveDriver();

        if ($filters = $validated[AssociatedIndex::FILTERS->value] ?? null) {
            /** @var array<string, array<string, mixed>> $filters */
            $filters = (array) $filters;
            if (in_array(SoftDeletes::class, class_uses_recursive($modelFQCN), true)) {
                $withDeleted = filter_var($filters['with_deleted'][Operators::EQ->value] ?? false, FILTER_VALIDATE_BOOLEAN);
                $onlyDeleted = filter_var($filters['only_deleted'][Operators::EQ->value] ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($onlyDeleted) {
                    /** @phpstan-ignore-next-line */
                    $query->onlyTrashed();
                } elseif ($withDeleted) {
                    /** @phpstan-ignore-next-line */
                    $query->withTrashed();
                }

                unset($filters['with_deleted'], $filters['only_deleted']);
            }

            $resources = Resource::generate($modelFQCN);
            $normalFilters = [];

            foreach ($filters as $field => $ops) {
                $filterConfig = $resources->filters[$field] ?? null;

                if ($filterConfig !== null && $filterConfig->isScope) {
                    $scopeValue = $ops[Operators::EQ->value] ?? null;
                    if ($scopeValue !== null && $scopeValue !== false && $scopeValue !== 'false' && $scopeValue !== '0') {
                        if ($filterConfig->type === 'boolean') {
                            $query->{$field}();
                        } else {
                            $query->{$field}($scopeValue);
                        }
                    }
                } else {
                    $normalFilters[$field] = $ops;
                }
            }

            if (! empty($normalFilters)) {
                self::applyFilters($query, $normalFilters, $locale, $driver);
            }
        }

        if ($sorts = $validated[AssociatedIndex::SORTS->value] ?? null) {
            foreach (Arr::dot((array) $sorts) as $field => $dir) {
                $direction = is_scalar($dir) && in_array((string) $dir, ['asc', 'desc'], true) ? (string) $dir : 'asc';
                $applied = $driver ? $driver->applySort($query, $field, $direction, $locale) : false;
                if (! $applied) {
                    $query->orderBy($field, $direction);
                }
            }
        }

        if ($fields = $validated[AssociatedIndex::FIELDS->value] ?? null) {
            $resources = Resource::generate($modelFQCN);
            $realFields = [];
            $aggregations = [];

            /** @var array<int, string> $fieldList */
            $fieldList = (array) $fields;
            foreach ($fieldList as $field) {
                $fieldConfig = $resources->fields[$field] ?? null;

                if ($fieldConfig === null) {
                    continue;
                }

                if ($fieldConfig->isAccessor) {
                    continue;
                }

                if ($fieldConfig->isAggregation) {
                    $aggregations[] = $fieldConfig;

                    continue;
                }

                $realFields[] = $field;
            }

            if (! empty($realFields)) {
                $query->select($realFields);
            }

            foreach ($aggregations as $agg) {
                $aggType = $agg->aggType;
                $relation = $agg->relation;
                if ($aggType === null || $relation === null) {
                    continue;
                }
                if ($aggType === 'count') {
                    $query->withCount($relation);
                } elseif ($aggType === 'exists') {
                    $query->withExists($relation);
                } else {
                    $method = 'with'.ucfirst($aggType);
                    $query->{$method}($relation, $agg->column);
                }
            }
        }

        if ($includes = $validated[AssociatedIndex::INCLUDES->value] ?? null) {
            /** @var array<string, \Closure|string> $with */
            $with = [];
            foreach ((array) $includes as $key => $value) {
                if (is_string($key) && is_array($value)) {
                    $with[$key] = function (EloquentBuilder $query) use ($value): void {
                        if (! empty($value['fields'])) {
                            $query->select((array) $value['fields']);
                        }
                    };
                } else {
                    $with[] = is_string($value) ? $value : (is_scalar($value) ? (string) $value : '');
                }
            }
            $query->with($with);
        }

        return $query;
    }

    /**
     * @param  Builder<Model>|class-string<Model>  $queryOrModel
     * @return LengthAwarePaginator<int, Model>
     */
    public static function paginateQuery(EloquentBuilder|string $queryOrModel, FormRequest|Request $request): LengthAwarePaginator
    {
        self::validateExtraParameters($request);

        $query = self::buildQuery($queryOrModel, $request);

        $validated = $request instanceof FormRequest ? $request->validated() : $request->all();
        $locale = app()->getLocale();
        $driver = QueryNormalizer::resolveDriver();

        /** @var array<string, mixed> $page */
        $page = (array) ($validated[AssociatedIndex::PAGE->value] ?? []);
        $limit = $page[AssociatedIndex::LIMIT->value] ?? 10;
        $number = $page[AssociatedIndex::NUMBER->value] ?? 1;
        $paginator = $query->paginate(
            is_numeric($limit) ? (int) $limit : 10,
            ['*'],
            AssociatedIndex::PAGE->value,
            is_numeric($number) ? (int) $number : 1
        );

        if ($fields = $validated[AssociatedIndex::FIELDS->value] ?? null) {
            /** @var class-string<Model> $modelFQCN */
            $modelFQCN = get_class($query->getModel());
            $resources = Resource::generate($modelFQCN);
            $accessorFields = [];
            foreach ((array) $fields as $field) {
                if (is_scalar($field) && isset($resources->fields[(string) $field]) && $resources->fields[(string) $field]->isAccessor) {
                    $accessorFields[] = (string) $field;
                }
            }
            if (! empty($accessorFields)) {
                $paginator->getCollection()->each->append($accessorFields);
            }
        }

        if ($driver) {
            $paginator->through(fn ($item) => $driver->translateItem($item, $locale));
        }

        return $paginator;
    }

    /**
     * @param  Builder<Model>|class-string<Model>  $queryOrModel
     * @return CursorPaginator<int, Model>
     */
    public static function cursorPaginateQuery(EloquentBuilder|string $queryOrModel, FormRequest|Request $request): CursorPaginator
    {
        self::validateExtraParameters($request);

        $query = self::buildQuery($queryOrModel, $request);

        $validated = $request instanceof FormRequest ? $request->validated() : $request->all();
        $locale = app()->getLocale();
        $driver = QueryNormalizer::resolveDriver();

        /** @var array<string, mixed> $page */
        $page = (array) ($validated[AssociatedIndex::PAGE->value] ?? []);

        $cursorValue = $page['cursor'] ?? null;
        $cursor = null;
        if (is_string($cursorValue)) {
            $cursor = Cursor::fromEncoded($cursorValue);
        }

        $cursorLimit = $page[AssociatedIndex::LIMIT->value] ?? 10;
        $cursorPaginator = $query->cursorPaginate(
            is_numeric($cursorLimit) ? (int) $cursorLimit : 10,
            ['*'],
            'page[cursor]',
            $cursor
        );

        if ($fields = $validated[AssociatedIndex::FIELDS->value] ?? null) {
            /** @var class-string<Model> $modelFQCN */
            $modelFQCN = get_class($query->getModel());
            $resources = Resource::generate($modelFQCN);
            $accessorFields = [];
            foreach ((array) $fields as $field) {
                if (is_scalar($field) && isset($resources->fields[(string) $field]) && $resources->fields[(string) $field]->isAccessor) {
                    $accessorFields[] = (string) $field;
                }
            }
            if (! empty($accessorFields)) {
                $cursorPaginator->getCollection()->each->append($accessorFields);
            }
        }

        if ($driver) {
            $cursorPaginator->through(fn ($item) => $driver->translateItem($item, $locale));
        }

        return $cursorPaginator;
    }

    /**
     * @param  EloquentBuilder<Model>|\Illuminate\Database\Query\Builder  $query
     * @param  array<string, array<string, mixed>>  $filters
     * @param  FieldResolver<Model>|null  $driver
     */
    private static function applyFilters(EloquentBuilder|\Illuminate\Database\Query\Builder $query, array $filters, string $locale, ?FieldResolver $driver, string $prefix = ''): void
    {
        foreach ($filters as $key => $value) {
            if (Operators::tryFrom((string) $key)) {
                $applied = $driver && $query instanceof EloquentBuilder ? $driver->applyFilter($query, $prefix, (string) $key, $value, $locale) : false;
                if (! $applied) {
                    Filter::build($query, $prefix, (string) $key, $value);
                }

                continue;
            }
            /** @var array<string, array<string, mixed>> $value */
            self::applyFilters($query, $value, $locale, $driver, $prefix === '' ? (string) $key : $prefix.'.'.$key);
        }
    }

    private static function validateExtraParameters(FormRequest|Request $request): void
    {
        /** @var array<string, mixed> $allInput */
        $allInput = (array) $request->all();
        /** @var array<string, mixed> $dottedInput */
        $dottedInput = Arr::dot($allInput);
        $allKeys = array_keys($dottedInput);
        /** @var array<string, mixed> $rules */
        $rules = $request instanceof FormRequest && method_exists($request, 'rules') ? $request->rules() : [];
        /** @var list<string> $ruleKeys */
        $ruleKeys = array_keys($rules);

        if (! empty($ruleKeys)) {
            $normalizedInputKeys = array_map(fn ($key) => preg_replace('/\.\d+$/', '.*', $key), $allKeys);
            $extra_parameters = array_diff($normalizedInputKeys, $ruleKeys);

            if (! empty($extra_parameters)) {
                $actualExtras = array_values(array_intersect_key($allKeys, array_intersect($normalizedInputKeys, $extra_parameters)));
                throw ValidationException::withMessages([
                    'extra_fields' => 'Unexpected parameter(s) key(s): '.implode(', ', $actualExtras),
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  class-string<Model>  $modelFQCN
     * @return array<string, mixed>
     */
    private static function castDataTypes(array $data, string $modelFQCN): array
    {
        $resources = Resource::generate($modelFQCN);
        /** @var array<string, array<string, mixed>> $filters */
        $filters = (array) ($data[AssociatedIndex::FILTERS->value] ?? []);

        foreach ($filters as $field => $ops) {
            $filterConfig = $resources->filters[$field] ?? null;
            $filterType = $filterConfig?->type;
            $type = $filterType instanceof AbstractType ? $filterType->value : ($filterType ?? 'string');
            $typedOps = [];

            foreach ($ops as $op => $val) {
                $typedOps[$op] = self::castValue($val, $type);
            }

            $filters[$field] = $typedOps;
        }

        $data[AssociatedIndex::FILTERS->value] = $filters;

        return $data;
    }

    private static function castValue(mixed $value, string $type): mixed
    {
        if ($value === null || $value === '' || $value === 'null') {
            return null;
        }

        if (is_array($value)) {
            return array_map(fn ($v) => self::castValue($v, $type), $value);
        }

        if (! is_scalar($value)) {
            // @phpstan-ignore-next-line cast from non-scalar mixed
            $value = (string) $value;
        }

        /** @var int|float|non-empty-string|bool $value */
        return match ($type) {
            'integer' => (int) $value,
            'numeric', 'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'date', 'datetime' => Carbon::parse((string) $value),
            default => $value,
        };
    }
}
