<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return is_array($this->resource)
            ? $this->resource
            : $this->resource->toArray();
    }
}
