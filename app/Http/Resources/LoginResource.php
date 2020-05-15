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
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'token' => $this->token,
            'role' => $this->roles()->first()->name
        ];
    }
}
