<?php

namespace App\Http\Resources\Pharmacy;

use App\Http\Resources\AreaResource;
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
        $lng = auth()->user()->info->lng;
        $lat = auth()->user()->info->lat;
        $branchLng = $this->lng;
        $branchLat = $this->lat;
        $distance =  intVal( 6367 * acos( cos( deg2rad($lat) ) * cos( deg2rad( $branchLat ) ) * cos( deg2rad( $branchLng ) - deg2rad($lng) ) + sin( deg2rad($lat) ) * sin( deg2rad( $branchLat ) ) ) );

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
            'area' => new AreaResource($this->area),
            'distance' => $distance
        ];
    }
}
