<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Admin\OrderStoreItemResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $items = $this->storeItems;
        $storeIds = [];
        $filteredItemsPerStore = [];
        foreach ($items as $item)
            if(!in_array($item->store_id, $storeIds))
                $storeIds[] = $item->store_id;

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
        return [
            'id' => $this->id,
            'items' => OrderStoreItemResource::collection($filteredItemsPerStore),
            'created_at' => $this->created_at,
            'cancelled' => $this->is_cancelled
        ];
    }
}
