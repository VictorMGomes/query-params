<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Facades;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Illuminate\Database\Eloquent\Model;

class ClassLoader
{
    public static function instanceModel(string $modelFQCN): Model
    {
        if (! is_subclass_of($modelFQCN, Model::class)) {
            throw new \InvalidArgumentException("{$modelFQCN} must be a subclass of ".Model::class);
        }

        $modelInstance = new $modelFQCN;

        return $modelInstance;
    }

    public static function findLoadedClass(string $namespacePrefix, string $className): string|false
    {
        $loaders = ComposerClassLoader::getRegisteredLoaders();
        $classLoader = reset($loaders);

        if (! $classLoader instanceof ComposerClassLoader) {
            throw new \RuntimeException('Could not retrieve Composer ClassLoader instance.');
        }

        $classMap = $classLoader->getClassMap();

        $namespacePrefix = trim($namespacePrefix, '\\');
        $className = trim($className, '\\');

        $pattern = '/^'.preg_quote($namespacePrefix, '/').'(?:\\\\[A-Za-z0-9_]+)*\\\\'.preg_quote($className, '/').'$/';

        $matches = preg_grep($pattern, array_keys($classMap));

        $class = is_array($matches) ? $matches : [];

        $class = reset($class) ?: false;

        return $class;
    }
}
