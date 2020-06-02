<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\StoreOrderCollection;
use App\Http\Resources\StoreOrderResource;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth-token');
        $this->middleware('auth-role:store')->except('index');
    }

    public function index(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';
        $orderType = ($request->has('order_type')) ? $request->order_type : 'ASC';

        return $this->handlePaginateResponse(1, OrderResource::collection(auth()->user()->orders()->paginate($limit)));
    }

    public function getStoreOrders(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';
        $orderType = ($request->has('order_type')) ? $request->order_type : 'ASC';

        $itemsIds =  auth()->user()->items()->where(function($query) use ($search){
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
}
