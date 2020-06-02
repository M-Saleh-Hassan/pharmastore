<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SearchItemResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'item' => $this->item,
            'user' => new UserBasicInfoResource($this->searchhistory->user),
            'searched_at' => $this->created_at
        ];
    }
}
