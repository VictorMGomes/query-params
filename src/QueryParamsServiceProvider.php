<?php

declare(strict_types=1);

namespace Victormgomes\QueryParams;

use Illuminate\Foundation\Http\FormRequest;
use ReflectionClass;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Victormgomes\QueryParams\Attributes\MapQueryParams;

class QueryParamsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('query-params')
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        $this->app->resolving(FormRequest::class, function (FormRequest $request) {
            $reflection = new ReflectionClass($request);
            $attributes = $reflection->getAttributes(MapQueryParams::class);

            if (! empty($attributes)) {
                /** @var MapQueryParams $attribute */
                $attribute = $attributes[0]->newInstance();

                // Automatically normalize the request with type-casting intelligence
                QueryBuilder::normalize($request, $attribute->model);
            }
        });
    }
}
