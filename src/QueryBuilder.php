<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Victormgomes\Queryparams\Enums\AssociatedIndex;
use Victormgomes\Queryparams\Helpers\Builder\Operations\Filter;
use Victormgomes\Queryparams\Helpers\ClassLoader;

class QueryBuilder
{
    public static function build(string $modelFQCN, FormRequest $request): LengthAwarePaginator
    {
        $extra_parameters = array_diff(array_keys($request->all()), array_keys($request->rules()));

        if (!empty($extra_parameters)) {
            throw ValidationException::withMessages([
                'extra_fields' => 'Unexpected parameter(s) key(s): ' . implode(', ', $extra_parameters),
            ]);
        }

        $validatedData = $request->validated();

        // Normalize {field}{op} => [field][op]
        $validated = collect($validatedData)->mapWithKeys(function ($value, $key) {
            // Replace {something} with [something]
            $normalizedKey = preg_replace('/\{([^}]+)\}/', '[$1]', $key);
            return [$normalizedKey => $value];
        })->toArray();

        // Convert the array-like string keys into an actual nested array
        $realArray = [];
        foreach ($validated as $key => $value) {
            // Split the key into its components, e.g., 'data[0][name]' -> ['data', '0', 'name']
            $keys = preg_split('/\[([^\]]+)\]/', $key, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

            if ($keys == false) {
                $keys = [];
            }

            // Use a reference to build the nested array
            $current = &$realArray;
            foreach ($keys as $k) {
                // If the key doesn't exist, create it as an array
                if (!isset($current[$k])) {
                    $current[$k] = [];
                }
                // Move the reference to the next level
                $current = &$current[$k];
            }
            // Assign the value at the deepest level
            $current = $value;
        }

        $model = ClassLoader::instanceModel($modelFQCN);

        $query = $model->newQuery();

        // The remaining logic now uses the $realArray
        // Filters
        if (isset($realArray[AssociatedIndex::FILTERS]) && is_array($realArray[AssociatedIndex::FILTERS])) {
            foreach ($realArray[AssociatedIndex::FILTERS] as $field => $conditions) {
                if (!is_array($conditions)) {
                    continue;
                }
                foreach ($conditions as $operator => $value) {
                    Filter::build($query, $field, $operator, $value);
                }
            }
        }

        // Sorting
        if (isset($realArray[AssociatedIndex::SORTS])) {
            foreach ($realArray[AssociatedIndex::SORTS] as $field => $direction) {
                $query->orderBy($field, $direction);
            }
        }

        // Field Selection
        if (isset($realArray[AssociatedIndex::FIELDS])) {
            $fields = $realArray[AssociatedIndex::FIELDS];
            if (is_array($fields)) {
                $query->select(array_keys($fields));
            }
        }

        // Includes / Relations
        if (isset($realArray[AssociatedIndex::INCLUDES])) {
            $includes = $realArray[AssociatedIndex::INCLUDES];
            if (is_array($includes)) {
                $query->with(array_keys($includes));
            }
        }

        $page = isset($realArray[AssociatedIndex::PAGE]) ? (array) $realArray[AssociatedIndex::PAGE] : [];

        $page_limit = isset($page[AssociatedIndex::LIMIT]) ? (int) $page[AssociatedIndex::LIMIT] : 10;
        $page_number = isset($page[AssociatedIndex::NUMBER]) ? (int) $page[AssociatedIndex::NUMBER] : 1;

        return $query->paginate($page_limit, ['*'], AssociatedIndex::PAGE, $page_number);
    }
}
