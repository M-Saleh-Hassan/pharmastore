<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CartCollection extends ResourceCollection
{

    public function toArray($request)
    {
        $filteredItemsPerStore = [];
        $items = $this->collection;
        $storeIds = [];
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
            'items' => CartResource::collection($filteredItemsPerStore),
            'total' => count($filteredItemsPerStore)
        ];
    }
}
