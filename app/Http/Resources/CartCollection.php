<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CartCollection extends ResourceCollection
{

    public function toArray($request)
    {
        return [
            'items' => CartResource::collection($this->collection),
            'count' => $this->collection->count()
        ];
    }
}
