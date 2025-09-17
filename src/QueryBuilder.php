<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Victormgomes\Queryparams\Enums\AssociatedIndex;
use Victormgomes\Queryparams\Helpers\Builder\Operations\Filter;
use Victormgomes\Queryparams\Resources\QueryResource;

class QueryBuilder
{
    public static function build(string $modelFQCN, FormRequest $request): QueryResource
    {
        $validated = $request->validated();

        $model = self::instanceModel($modelFQCN);

        $query = $model->newQuery();

        // Filters
        if (isset($validated[AssociatedIndex::FILTERS]) && is_array($validated[AssociatedIndex::FILTERS])) {
            foreach ($validated[AssociatedIndex::FILTERS] as $field => $conditions) {
                if (! is_array($conditions)) {
                    continue;
                }
                foreach ($conditions as $operator => $value) {
                    Filter::build($query, $field, $operator, $value);
                }
            }
        }

        // Sorting
        if (isset($validated[AssociatedIndex::SORTS]) && is_array($validated[AssociatedIndex::SORTS])) {
            foreach ($validated[AssociatedIndex::SORTS] as $field => $direction) {
                $query->orderBy($field, $direction);
            }
        }

        // Field Selection
        if (isset($validated[AssociatedIndex::FIELDS])) {
            $fields = $validated[AssociatedIndex::FIELDS];
            if (is_array($fields)) {
                $query->select($fields);
            }
        }

        // Includes / Relations
        if (isset($validated[AssociatedIndex::INCLUDES])) {
            $includes = $validated[AssociatedIndex::INCLUDES];
            if (! is_array($includes)) {
                $includes = [$includes];
            }
            $query->with($includes);
        }

        $page = isset($validated[AssociatedIndex::PAGE]) ? (array) $validated[AssociatedIndex::PAGE] : [];

        $page_limit = isset($page[AssociatedIndex::LIMIT]) ? (int) $page[AssociatedIndex::LIMIT] : 10;
        $page_number = isset($page[AssociatedIndex::NUMBER]) ? (int) $page[AssociatedIndex::NUMBER] : 1;

        $paginatedQuery = $query->paginate($page_limit, ['*'], AssociatedIndex::PAGE, $page_number);

        return new QueryResource($paginatedQuery);
    }

    private static function instanceModel(string $modelFQCN): Model
    {
        if (! is_subclass_of($modelFQCN, Model::class)) {
            throw new \InvalidArgumentException("{$modelFQCN} must be a subclass of ".Model::class);
        }

        $modelInstance = new $modelFQCN;

        return $modelInstance;
    }
}
