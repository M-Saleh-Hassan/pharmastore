<?php

namespace App\Http\Resources;

use App\Http\Resources\Pharmacy\BranchResource;
use Illuminate\Database\Eloquent\Builder;
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
        $followingStoresIds = auth()->user()->following()->pluck('users.id')->toArray();
        $isFollowed = in_array($this->id, $followingStoresIds) ? 1 : 0;
        $branches = $this->branches();
        if($request->has('city_id'))
            $branches = $branches->whereHas('area', function (Builder $query) use ($request) {
                $query->where('city_id', $request->city_id);
            });
        if($request->has('area_id'))
            $branches = $branches->where('area_id', $request->area_id);
        $branches = $branches->get();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'bio' => (isset($this->info)) ? $this->info->bio : null,
            'delivery_details' => (isset($this->info)) ? $this->info->delivery_details : null,
            'branches' => BranchResource::collection($branches),
            'is_followed' => $isFollowed
        ];
    }
}
