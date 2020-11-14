<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserBasicInfoResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'username'         => $this->username,
            'email'            => $this->email,
            'role'             => $this->roles()->first()->name,
            'lng'              => isset($this->info) ? $this->info->lng : null,
            'lat'              => isset($this->info) ? $this->info->lat : null,
            'bio'              => isset($this->info) ? $this->info->bio : null,
            'delivery_details' => isset($this->info) ? $this->info->delivery_details : null,
            'mobile1'          => isset($this->info) ? $this->info->mobile1 : null,
            'mobile2'          => isset($this->info) ? $this->info->mobile2 : null,
            'branches'         => BranchResource::collection($this->branches),
            'is_blocked'       => $this->is_blocked,
            'area'             => $this->when(isset($this->area), new AreaResource($this->area)),
            'address'          => $this->when($this->address, $this->address)
        ];
    }
}
