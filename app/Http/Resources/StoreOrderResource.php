<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreOrderResource extends JsonResource
{
    public function toArray($request)
    {
        $items = $this->storeItems;
        $storeIds = [];
        $filteredItemsPerStore = [];
        foreach ($items as $item)
            if(!in_array($item->store_id, $storeIds))
                $storeIds[] = $item->store_id;

        foreach ($storeIds as $storeId) {
            $store = User::find($storeId);;
            $filteredCollection = app()->make('stdClass');
            $filteredCollection->store_id = $storeId;
            $filteredCollection->store_name = $store->name;
            $filteredCollection->store_email = $store->email;
            $filteredCollection->store_mobile = $store->info->mobile1;
            $filteredCollection->items = $items->filter(function ($item, $key) use ($storeId) {
                return $item->store_id ==  $storeId;
            });
            $filteredItemsPerStore[] = $filteredCollection;
        }
        return [
            'id' => $this->id,
            'user' => new UserBasicInfoResource($this->user),
            'items' => OrderStoreItemResource::collection($filteredItemsPerStore),
            'cancelled' => $this->is_cancelled
        ];
    }
}
