<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    private $token;

    public function __construct($resource, $token)
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;

        $this->token = $token;
    }

    public function toArray($request)
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'username'         => $this->username,
            'email'            => $this->email,
            'token'            => $this->token,
            'role'             => $this->roles()->first()->name,
            'lng'              => isset($this->info) ? $this->info->lng : null,
            'lat'              => isset($this->info) ? $this->info->lat : null,
            'bio'              => isset($this->info) ? $this->info->bio : null,
            'delivery_details' => isset($this->info) ? $this->info->delivery_details : null,
            'mobile1'          => isset($this->info) ? $this->info->mobile1 : null,
            'mobile2'          => isset($this->info) ? $this->info->mobile2 : null,
            'area'             => $this->when(isset($this->area), new AreaResource($this->area)),
            'address'          => $this->when($this->address, $this->address)
        ];
    }
}
