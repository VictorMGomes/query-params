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

        if (! empty($extra_parameters)) {
            throw ValidationException::withMessages([
                'extra_fields' => 'Unexpected parameter(s) key(s): '.implode(', ', $extra_parameters),
            ]);
        }

        $validatedData = $request->validated();

        // Normalize {field}{op} => [field][op]
        $validated = collect($validatedData)->mapWithKeys(function ($value, $key) {
            $normalizedKey = preg_replace('/\{([^}]+)\}/', '[$1]', $key);

            return [$normalizedKey => $value];
        })->toArray();

        // Convert the array-like string keys into an actual nested array
        $realArray = [];
        foreach ($validated as $key => $value) {
            $keys = preg_split('/\[([^\]]+)\]/', $key, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

            if ($keys == false) {
                $keys = [];
            }

            $current = &$realArray;
            foreach ($keys as $k) {
                if (! isset($current[$k])) {
                    $current[$k] = [];
                }
                $current = &$current[$k];
            }
            $current = $value;
        }

        $model = ClassLoader::instanceModel($modelFQCN);
        $query = $model->newQuery();

        // --- Configuração de Translatable ---
        // Detecta se o model usa translatable e define quais campos são traduzíveis
        $translatableFields = [];
        $locale = app()->getLocale();

        if (property_exists($model, 'translatable') && is_array($model->translatable)) {
            $translatableFields = $model->translatable;
        }

        // --- Filters ---
        // Mantido: O filtro deve ocorrer no nível do banco de dados (SQL)
        if (isset($realArray[AssociatedIndex::FILTERS]) && is_array($realArray[AssociatedIndex::FILTERS])) {
            foreach ($realArray[AssociatedIndex::FILTERS] as $field => $conditions) {
                if (! is_array($conditions)) {
                    continue;
                }

                $targetField = in_array($field, $translatableFields)
                    ? "{$field}->{$locale}"
                    : $field;

                foreach ($conditions as $operator => $value) {
                    Filter::build($query, $targetField, $operator, $value);
                }
            }
        }

        // --- Sorting ---
        // Mantido: A ordenação deve ocorrer no nível do banco de dados (SQL)
        if (isset($realArray[AssociatedIndex::SORTS])) {
            foreach ($realArray[AssociatedIndex::SORTS] as $field => $direction) {
                $targetField = in_array($field, $translatableFields)
                    ? "{$field}->{$locale}"
                    : $field;

                $query->orderBy($targetField, $direction);
            }
        }

        // --- Field Selection (CORRIGIDO) ---
        // Alterado: Removemos a transformação SQL "-> as".
        // Selecionamos o JSON puro para garantir a hidratação correta do Model.
        if (isset($realArray[AssociatedIndex::FIELDS])) {
            $fields = $realArray[AssociatedIndex::FIELDS];
            if (is_array($fields)) {
                $requestedFields = array_keys($fields);
                $query->select($requestedFields);
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

        $paginator = $query->paginate($page_limit, ['*'], AssociatedIndex::PAGE, $page_number);

        // --- Transformação de Saída (NOVO) ---
        // Intercepta os itens da página atual e converte os campos translatable para string
        if (! empty($translatableFields)) {
            $paginator->through(function ($item) use ($translatableFields, $locale) {
                // Converte o model para array (respeitando $visible/$hidden)
                $data = $item->toArray();

                foreach ($translatableFields as $field) {
                    // Se o campo existe no array final (foi selecionado), aplicamos a tradução
                    if (array_key_exists($field, $data)) {
                        // Usa o método getTranslation do pacote Spatie para pegar a string correta
                        $data[$field] = $item->getTranslation($field, $locale);
                    }
                }

                return $data;
            });
        }

        return $paginator;
    }
}
