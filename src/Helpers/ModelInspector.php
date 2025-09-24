<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers;

use Illuminate\Database\Eloquent\Relations\Relation;

class ModelInspector
{
    public static function info(string $modelFQCN): array
    {
        $model = ClassLoader::instanceModel($modelFQCN);

        $info = [
            'class' => get_class($model),
            'table' => $model->getTable(),
            'primary_key' => $model->getKeyName(),
            'visible' => $model->getVisible(),
            'hidden' => $model->getHidden(),
            'fillable' => $model->getFillable(),
            'appends' => $model->getAppends(),
            'guarded' => $model->getGuarded(),
            'casts' => $model->getCasts(),
            'relations' => self::getModelRelations($model)
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

    // Se visible estiver definido, priorizar apenas os campos visíveis
    if (!empty($info['visible'])) {
        $allFields = array_merge(
            $info['visible'],
            $info['appends'] ?? []
        );
    } else {
        // Caso contrário, coletar todos os campos
        $allFields = array_merge(
            $info['fillable'] ?? [],
            $info['appends'] ?? [],
            is_array($info['primary_key']) ? $info['primary_key'] : [$info['primary_key'] ?? []],
            array_keys($info['casts'] ?? []),
            !empty($info['timestamps']['created_at']) ? [$info['timestamps']['created_at']] : [],
            !empty($info['timestamps']['updated_at']) ? [$info['timestamps']['updated_at']] : [],
            isset($info['timestamps']['deleted_at']) ? [$info['timestamps']['deleted_at']] : []
        );

        // Remove hidden apenas se visible não estiver definido
        $hidden = $info['hidden'] ?? [];
        $allFields = array_diff($allFields, $hidden);
    }

    // Monta os campos com seus tipos
    foreach (array_unique($allFields) as $field) {
        $fields[$field] = [
            'type' => $info['casts'][$field] ?? 'string',
        ];
    }

    // Adiciona relações como "relation"
    foreach ($info['relations'] as $relation) {
        $fields[$relation] = [
            'type' => 'relation',
        ];
    }

    foreach ($info['appends'] as $append) {
        $fields[$append] = [
            'type' => 'append',
        ];
    }

        ksort($fields);

    return $fields;
}


}
