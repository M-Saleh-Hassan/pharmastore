<?php

namespace App\Http\Resources\Admin;

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

        $branch = $item->branch;

        return [
            'item_id' => $item->id,
            'branch_name' => $branch->name,
            'quantity' => $this->quantity,
            'basic_price' => $item->basic_price,
            'discount' => $item->discount,
            'name_en' => $item->name_en,
            'name_ar' => $item->name_ar
        ];
    }
}
