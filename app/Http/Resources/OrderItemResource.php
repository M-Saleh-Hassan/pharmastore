<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'id' => $this->item->id,
            'quantity' => $this->quantity,
            'basic_price' => $this->item->basic_price,
            'discount' => $this->item->discount,
            'name_en' => $this->item->name_en,
            'name_ar' => $this->item->name_ar
        ];
    }
}
