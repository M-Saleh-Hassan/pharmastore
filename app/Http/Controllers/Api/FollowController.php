<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth-token', 'auth-role:pharmacy']);
    }

    public function getAllStores(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';

        $stores = User::orderBy($orderBy)->whereHas('roles', function (Builder $query) {
            $query->where('name', 'store');
        })->where(function($query) use ($search){
            $query->where('name', 'like', '%'.$search.'%' );
        })->paginate($limit);

        return $this->handlePaginateResponse(1, StoreResource::collection($stores));
    }

    public function follow(User $store)
    {
        if(!$store->isStore())
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'can\'t follow any other types other than stores.']], 401));

        if(!auth()->user()->following->contains($store->id))
            auth()->user()->following()->attach($store->id);

        return $this->handleResponse(1, 'Store is followed successfully.');
    }

    public function unfollow(User $store)
    {
        if(!$store->isStore())
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'can\'t follow any other types other than stores.']], 401));

        if(auth()->user()->following->contains($store->id))
            auth()->user()->following()->detach($store->id);

        return $this->handleResponse(1, 'Store is unfollowed successfully.');
    }

    public function getAllStoreItems(Request $request, User $store)
    {
        if(!$store->isStore())
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'id passed isn\'t a store']], 401));

        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';
        $orderType = ($request->has('order_type')) ? $request->order_type : 'ASC';

        $items = $store->items()->orderBy($orderBy, $orderType)->where(function($query) use ($search){
            $query->where('name_en', 'like', '%'.$search.'%' )
            ->orWhere('name_ar', 'like', '%'.$search.'%');
        })->paginate($limit);

        return $this->handlePaginateResponse(1, $items);
    }
}
