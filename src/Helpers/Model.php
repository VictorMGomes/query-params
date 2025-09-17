<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers;

use Illuminate\Support\Str;

class Model
{
    public static function getModel(object $instance): string
    {
        // Get the base class name, e.g., UserIndexRequest => User
        $name = class_basename($instance);
        $modelName = Str::before($name, 'IndexRequest');

        // First try the plural folder (e.g., App\Models\Users\User)
        $pluralNamespace = 'App\\Models\\'.Str::plural($modelName)."\\{$modelName}";
        if (class_exists($pluralNamespace)) {
            return $pluralNamespace;
        }

        // Fallback to the singular folder (e.g., App\Models\User)
        $singularNamespace = "App\\Models\\{$modelName}";
        if (class_exists($singularNamespace)) {
            return $singularNamespace;
        }

        // Optional: throw an exception if neither exists
        throw new \Exception("Model class not found for {$modelName}");
    }

    public static function getTableName(object $instance): string
    {
        $modelName = self::getModel($instance);

        $model = new $modelName;

        return $model->getTable();
    }
}
