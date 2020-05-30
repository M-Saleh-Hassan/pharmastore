<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';
        $orderType = ($request->has('order_type')) ? $request->order_type : 'ASC';

        return $this->handlePaginateResponse(1, OrderResource::collection(auth()->user()->orders()->paginate($limit)));
    }
}
