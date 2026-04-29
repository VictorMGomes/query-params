<?php

declare(strict_types=1);

namespace Victormgomes\QueryParams\Concerns;

use ReflectionClass;
use Victormgomes\QueryParams\Attributes\MapQueryParams;
use Victormgomes\QueryParams\QueryBuilder;
use Victormgomes\QueryParams\Rules;

trait HasQueryParams
{
    public function prepareForValidation(): void
    {
        QueryBuilder::normalize($this, $this->resolveModelFQCN());
    }

    public function rules(): array
    {
        $model = $this->resolveModelFQCN();

        return $model ? Rules::generate($model) : [];
    }

    protected function resolveModelFQCN(): ?string
    {
        $reflection = new ReflectionClass($this);
        $attributes = $reflection->getAttributes(MapQueryParams::class);

        if (! empty($attributes)) {
            /** @var MapQueryParams $attribute */
            $attribute = $attributes[0]->newInstance();
            if ($attribute->model) {
                return $attribute->model;
            }
        }

        if (method_exists($this, 'model')) {
            return $this->model();
        }

        return null;
    }
}
