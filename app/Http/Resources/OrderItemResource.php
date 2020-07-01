<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $item = $this->item()->withTrashed()->first();

        $lng = auth()->user()->info->lng;
        $lat = auth()->user()->info->lat;
        $branch = $item->branch;
        $branchLng = $branch->lng;
        $branchLat = $branch->lat;
        $distance =  intVal( 6367 * acos( cos( deg2rad($lat) ) * cos( deg2rad( $branchLat ) ) * cos( deg2rad( $branchLng ) - deg2rad($lng) ) + sin( deg2rad($lat) ) * sin( deg2rad( $branchLat ) ) ) );
        
        return [
            'item_id' => $item->id,
            'branch_name' => $branch->name,
            'distance' => $distance,
            'quantity' => $this->quantity,
            'basic_price' => $item->basic_price,
            'discount' => $item->discount,
            'name_en' => $item->name_en,
            'name_ar' => $item->name_ar
        ];
    }
}
