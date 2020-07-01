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
        $item = $this->item()->withTrashed()->first();
        return [
            'item_id' => $item->id,
            'quantity' => $this->quantity,
            'basic_price' => $item->basic_price,
            'discount' => $item->discount,
            'name_en' => $item->name_en,
            'name_ar' => $item->name_ar
        ];
    }
}
