<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'store_id' => $this->store_id,
            'store_name' => $this->store_name,
            'items' => CartItemResource::collection($this->items)
        ];
    }
}
