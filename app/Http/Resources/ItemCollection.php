<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'items' => ItemResource::collection($this->collection),
            'total' => $this->count
        ];
    }
}
