<?php

declare(strict_types=1);

namespace Victormgomes\Queryparams\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class QueryResource extends ResourceCollection
{
    protected LengthAwarePaginator $paginatedItem;

    public function __construct(LengthAwarePaginator $paginatedItem)
    {
        $this->paginatedItem = $paginatedItem;
    }

    public function toArray(Request $request): array
    {
        return [
            'collection' => CollectionResource::collection($this->paginatedItem->items()),
            'total_items' => $this->paginatedItem->total(),
            'first_item' => $this->paginatedItem->firstItem(),
            'last_item' => $this->paginatedItem->lastItem(),
            'per_page' => $this->paginatedItem->perPage(),
            'current_page' => $this->paginatedItem->currentPage(),
            'last_page' => $this->paginatedItem->lastPage(),
        ];
    }
}
