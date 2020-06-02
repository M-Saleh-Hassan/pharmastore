<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => new UserBasicInfoResource($this->user),
            'items' => OrderItemResource::collection($this->storeItems)
        ];
    }
}
