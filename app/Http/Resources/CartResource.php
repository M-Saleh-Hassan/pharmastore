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
            'store_email' => $this->store_email,
            'store_mobile' => $this->store_mobile,
            'items' => CartItemResource::collection($this->items)
        ];
    }
}
