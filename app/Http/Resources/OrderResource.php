<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $filteredItemsPerStore = [];
        if(!empty($request->order)) {
            $items = $this->items()->withTrashed()->get();
            $storeIds = [];
            foreach ($items as $item)
                if(!in_array($item->store_id, $storeIds))
                    $storeIds[] = $item->store_id;
            dd('');
            foreach ($storeIds as $storeId) {
                $storeName = User::find($storeId)->name;
                $filteredCollection = app()->make('stdClass');
                $filteredCollection->store_id = $storeId;
                $filteredCollection->store_name = $storeName;
                $filteredCollection->items = $items->filter(function ($item, $key) use ($storeId) {
                    return $item->store_id ==  $storeId;
                });
                $filteredItemsPerStore[] = $filteredCollection;
            }
        }
        return [
            'order_id' => $this->id,
            'items' => $this->when(!empty($request->order), OrderStoreItemResource::collection($filteredItemsPerStore)),
            'created_at' => $this->created_at,
            'is_cancelled' => $this->is_cancelled
        ];
    }
}
