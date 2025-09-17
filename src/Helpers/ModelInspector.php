<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class ModelInspector
{
    public static function info(string $modelClass): array
    {
        /** @var Model $model */
        $model = new $modelClass;

        $info = [
            'class' => $modelClass,
            'table' => $model->getTable(),
            'primary_key' => $model->getKeyName(),
            'fillable' => $model->getFillable(),
            'hidden' => $model->getHidden(),
            'guarded' => $model->getGuarded(),
            'casts' => $model->getCasts(),
            'relations' => self::getModelRelations($model),
        ];

        // Adicionar campos de timestamp se estiverem habilitados
        if ($model->timestamps) {
            $info['timestamps'] = [
                'created_at' => $model::CREATED_AT,
                'updated_at' => $model::UPDATED_AT,
            ];

            if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model))) {
                $deletedAtColumn = property_exists($model, 'deletedAt') ? $model->getDeletedAtColumn() : 'deleted_at';
                $info['timestamps']['deleted_at'] = $deletedAtColumn;
            }
        }

        return $info;
    }

    public static function getModelRelations($model)
    {
        $class = new \ReflectionClass($model);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        $relations = [];

        foreach ($methods as $method) {
            // Skip inherited methods, we only care about methods defined in the model
            if ($method->class !== get_class($model)) {
                continue;
            }

            // Skip methods that require parameters
            if ($method->getNumberOfParameters() > 0) {
                continue;
            }

            // Invoke method safely
            try {
                $return = $method->invoke($model);
                if ($return instanceof Relation) {
                    $relations[] = $method->getName();
                }
            } catch (\Throwable $e) {
                // Ignore methods that can’t be invoked cleanly
            }
        }

        return $relations;
    }

    public static function fieldsSummary(string $modelClass): array
    {
        $info = self::info($modelClass);

        $fields = [];

        // Step 1: Collect all attribute keys
        $allFields = array_merge(
            $info['fillable'] ?? [],
            array_keys($info['casts'] ?? []),
            $info['timestamps']['created_at'] ?? [] ? [$info['timestamps']['created_at']] : [],
            $info['timestamps']['updated_at'] ?? [] ? [$info['timestamps']['updated_at']] : [],
            isset($info['timestamps']['deleted_at']) ? [$info['timestamps']['deleted_at']] : []
        );

        // Step 2: Add normal fields (fillable, casts, timestamps)
        foreach (array_unique($allFields) as $field) {
            $fields[$field] = [
                'type' => $info['casts'][$field] ?? null,
            ];
        }

        // Step 3: Add relations as fields with type = relation
        foreach ($info['relations'] as $relation) {
            $fields[$relation] = [
                'type' => 'relation',
            ];
        }

        ksort($fields);

        return $fields;
    }
}
