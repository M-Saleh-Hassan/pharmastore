<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreUploadHistoryResource extends JsonResource
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
            'branch' => $this->branch,
            'items' => $this->items()->withTrashed()->get(),
            'uploaded_at' => $this->created_at
        ];
    }
}
