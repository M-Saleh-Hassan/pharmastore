<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Http\Resources\StoreWithFollowingForPharmacyResource;
use App\Http\Resources\UserBasicInfoResource;
use App\Models\Branch;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth-token', 'auth-role:pharmacy']);
    }

    public function getAllStores(Request $request)
    {
        $this->validation($request, [
            'order_by' => 'in:distance,id',
            'order_type' => 'in:asc,desc'
        ], [
            'order_by.in' => 'order_by must have value of distance or id.',
            'order_type.in' => 'order_by must have value of asc or desc.'
        ]);

        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';
        $orderType = ($request->has('order_type')) ? $request->order_type : 'asc';

        $lng = auth()->user()->info->lng;
        $lat = auth()->user()->info->lat;

        $stores = User::join('branches', 'branches.store_id', '=', 'users.id')
        ->select(DB::raw('users.id, MIN( 6367 * acos( cos( radians('.$lat.') ) * cos( radians( branches.lat ) ) * cos( radians( branches.lng ) - radians('.$lng.') ) + sin( radians('.$lat.') ) * sin( radians( branches.lat ) ) ) ) AS distance'))
        ->orderBy($orderBy, $orderType)
        ->whereHas('roles', function (Builder $query) {
            $query->where('roles.name', 'store');
        })->where(function($query) use ($search){
            $query->where('users.name', 'like', '%'.$search.'%' );
        })->groupBy(['users.id'])->paginate($limit);

        return $this->handlePaginateResponse(1, StoreResource::collection($stores));
    }

    public function getStoreInfo(User $store)
    {
        if(!$store->isStore())
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'id passed isn\'t a store']], 401));

        return $this->handleResponse(1, new StoreWithFollowingForPharmacyResource($store));
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

        if($request->has('branch')) {
            $branch = Branch::findOrFail($request->branch);
            if($branch->store_id != $store->id)
                throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'this branch isn\'t in this store ']], 401));
        }

        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';
        $orderType = ($request->has('order_type')) ? $request->order_type : 'ASC';

        $items = $store->items();
        if($request->has('branch'))
            $items = $items->where('branch_id', $request->branch);
        $items = $items->orderBy($orderBy, $orderType)->where(function($query) use ($search){
            $query->where('name_en', 'like', '%'.$search.'%' )
            ->orWhere('name_ar', 'like', '%'.$search.'%');
        })->paginate($limit);

        return $this->handlePaginateResponse(1, $items);
    }

    public function getFollowingStoresItems(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;
        $search = ($request->has('search')) ? $request->search : '';

        $followingStoresIds = auth()->user()->following()->pluck('users.id');
        $items = Item::join('branches', 'items.branch_id', '=', 'branches.id')
        ->join('users', 'users.id', '=', 'branches.store_id')
        ->select('items.*')
        ->where(function($query) use ($search){
            $query->where('items.name_en', 'like', '%'.$search.'%' )
            ->orWhere('items.name_ar', 'like', '%'.$search.'%');
        })->whereIn('users.id', $followingStoresIds)
        ->latest()
        ->paginate($limit);

        return $this->handlePaginateResponse(1, $items);
    }
}
