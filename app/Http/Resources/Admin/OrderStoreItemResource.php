<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderStoreItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'store_id' => $this->store_id,
            'store_name' => $this->store_name,
            'store_email' => $this->store_email,
            'store_mobile' => $this->store_mobile,
            'items' => OrderItemResource::collection($this->items)
        ];
    }
}
