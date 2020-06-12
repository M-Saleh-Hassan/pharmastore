<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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
            'username' => $this->username,
            'bio' => (isset($this->info)) ? $this->info->bio : null,
            'delivery_details' => (isset($this->info)) ? $this->info->delivery_details : null,
            'branches' => BranchResource::collection($this->branches),
            'distance' => $this->when(isset($this->distance), intVal($this->distance)),
        ];
    }
}
