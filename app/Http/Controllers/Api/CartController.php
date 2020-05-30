<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartCollection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Rules\QunatityAvailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth-token', 'auth-role:pharmacy']);
    }

    public function index()
    {
        return $this->handleResponse(1, new CartCollection(auth()->user()->cart));
    }

    // add to cart
    public function store(Request $request)
    {
        $this->validation($request, [
            'item_id' => 'required|exists:items,id',
            'quantity' => ['required', 'int', new QunatityAvailable($request->item_id)]
        ]);

        DB::beginTransaction();
        $order = Order::firstOrCreate(['user_id' => auth()->user()->id, 'is_cart' => 1]);
        $item = OrderItem::updateOrCreate([
            'order_id' => $order->id,
            'item_id'  => $request->item_id
        ],[
            'order_id' => $order->id,
            'item_id'  => $request->item_id,
            'quantity' => $request->quantity
        ]);
        DB::commit();

        return $this->handleResponse(1, new CartCollection($order->items));
    }

    public function destroy(Request $request, OrderItem $item)
    {
        $order = $item->order;
        $item->delete();
        return $this->handleResponse(1, new CartCollection($order->items));
    }

    public function makeOrder()
    {
        $order = Order::where('user_id', auth()->user()->id)->where('is_cart', 1)->firstOrFail();
        $order->is_cart = 0;
        $order->save();

        return $this->handleResponse(1, 'Your cart is turned into order.');
    }
}
