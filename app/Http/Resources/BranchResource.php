<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
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
            'name' => $this->name,
            'lng' => $this->lng,
            'lat' => $this->lat,
            'mobile' => $this->mobile,
            'working_hours_from' => $this->working_hours_from ,
            'working_hours_to' => $this->working_hours_to,
            'working_days_from' => $this->working_days_from,
            'working_days_to' => $this->working_days_to,
            'area' => $this->area,
            'items' => $this->items
        ];
    }
}
