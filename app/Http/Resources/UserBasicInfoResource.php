<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserBasicInfoResource extends JsonResource
{
    
    public function toArray($request)
    {
        $returnArray = [
            'id'       => $this->id,
            'name'     => $this->name,
            'username' => $this->username,
            'email'    => $this->email,
            'role'     => $this->roles()->first()->name
        ];
        if($this->info) {
            $additionalParametersArray = [
                'lng'              => $this->info->lng,
                'lat'              => $this->info->lat,
                'bio'              => $this->info->bio,
                'delivery_details' => $this->info->delivery_details
            ];
            $returnArray = array_merge($returnArray, $additionalParametersArray);          }
        return $returnArray;
    }
}
