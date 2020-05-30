<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
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
            'id' => $this->id,
            'quantity' => $this->quantity,
            'basic_price' => $this->basic_price,
            'discount' => $this->discount,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'branch' => [
                'id' => $this->branch->id,
                'name' =>$this->branch->name,
            ],
            'store' => [
                'id' => $this->branch->store->id,
                'name' => $this->branch->store->name
            ],
            'city' => [
                'id' => $this->branch->area->city->id,
                'name_en' => $this->branch->area->city->name_en,
                'name_ar' => $this->branch->area->city->name_ar
            ],
            'distance' => $this->when(isset($this->distance), intVal($this->distance))
        ];
    }
}
