<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
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
            'city_id' => $this->city_id,
            'city_name_en' => $this->city->name_en,
            'city_name_ar' => $this->city->name_ar,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
        ];
    }
}
