<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
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
            'order_item_id' => $this->id,
            'item_id' => $this->item->id,
            'quantity' => $this->quantity,
            'basic_price' => $this->item->basic_price,
            'discount' => $this->item->discount,
            'name_en' => $this->item->name_en,
            'name_ar' => $this->item->name_ar,
            'branch_id' => $this->item->branch->id,
            'branch_name' => $this->item->branch->name,
        ];
    }
}
