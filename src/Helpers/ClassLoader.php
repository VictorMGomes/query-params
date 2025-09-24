<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Helpers;

use Illuminate\Database\Eloquent\Model;

class ClassLoader
{
    public static function instanceModel(string $modelFQCN): Model
    {
        if (! is_subclass_of($modelFQCN, Model::class)) {
            throw new \InvalidArgumentException("{$modelFQCN} must be a subclass of " . Model::class);
        }

        $modelInstance = new $modelFQCN;

        return $modelInstance;
    }
}
