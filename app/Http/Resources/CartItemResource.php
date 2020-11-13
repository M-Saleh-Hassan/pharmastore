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
        $item = $this->item;
        if(is_null($item)) $item = $this->item()->withTrashed()->first();

        $branch = $item->branch;
        if(is_null($branch)) $branch = $item->branch()->withTrashed()->first();

        return [
            'order_item_id' => $this->id,
            'item_id' => $item->id,
            'quantity' => $this->quantity,
            'basic_price' => $item->basic_price,
            'discount' => $item->discount,
            'name_en' => $item->name_en,
            'name_ar' => $item->name_ar,
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
        ];
    }
}
