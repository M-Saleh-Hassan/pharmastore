<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SearchItemResource;
use App\Http\Resources\StoreOrderResource;
use App\Http\Resources\UserBasicInfoResource;
use App\Models\Item;
use App\Models\Order;
use App\Models\SearchHistoryResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth-token', 'auth-role:admin']);
    }

    public function getAllUsers(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';
        $orderType = ($request->has('order_type')) ? $request->order_type : 'ASC';

        $users = User::search($search)->notAdmin()->paginate($limit);

        return $this->handlePaginateResponse(1, UserBasicInfoResource::collection($users));
    }

    public function getAllOrders(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';
        $orderType = ($request->has('order_type')) ? $request->order_type : 'ASC';

        $itemsIds =  Item::where(function($query) use ($search){
            $query->where('name_en', 'like', '%'.$search.'%' )
            ->orWhere('name_ar', 'like', '%'.$search.'%');
        })->pluck('items.id');

        $orders = Order::order()->whereHas('items', function (Builder $query) use ($itemsIds) {
            $query->whereIn('item_id', $itemsIds);
        })->paginate($limit);

        foreach ($orders as $order) {
            $order->storeItems = $order->items()->whereIn('item_id', $itemsIds)->get();
        }

        return $this->handlePaginateResponse(1, StoreOrderResource::collection($orders));
    }

    public function getSearchHistory(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';
        $orderType = ($request->has('order_type')) ? $request->order_type : 'DESC';

        $items = SearchHistoryResult::search($search)->orderBy($orderBy, $orderType)->paginate($limit);
        return $this->handlePaginateResponse(1, SearchItemResource::collection($items));
    }

}
