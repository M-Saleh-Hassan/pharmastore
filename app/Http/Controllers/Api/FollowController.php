<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
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
            'order_type' => 'in:asc,desc',
            'city_id' => 'exists:cities,id',
            'area_id' => 'exists:areas,id'
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
            ->join('areas', 'areas.id', '=', 'branches.area_id')
            ->join('cities', 'cities.id', '=', 'areas.city_id')
            ->select(DB::raw('users.id, users.name, users.username, MIN( 6367 * acos( cos( radians('.$lat.') ) * cos( radians( branches.lat ) ) * cos( radians( branches.lng ) - radians('.$lng.') ) + sin( radians('.$lat.') ) * sin( radians( branches.lat ) ) ) ) AS distance'))
            ->orderBy($orderBy, $orderType)
            ->where('users.is_blocked', 0)
            ->whereHas('roles', function (Builder $query) {
                $query->where('roles.name', 'store');
            })->where(function($query) use ($search){
                $query->where('users.name', 'like', '%'.$search.'%' );
            });
        if($request->has('city_id'))
            $stores = $stores->where('cities.id', $request->city_id);
        if($request->has('area_id'))
            $stores = $stores->where('areas.id', $request->area_id);
        if($request->has('is_followed') && $request->is_followed == 1) {
            $followingStoresIds = auth()->user()->following()->pluck('users.id')->toArray();
            $stores = $stores->whereIn('users.id', $followingStoresIds);
        }
        $stores = $stores->groupBy(['users.id', 'users.name', 'users.username'])->paginate($limit);

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
        $this->validation($request, [
            'order_by' => 'in:distance,store,city,area,name_en,name_ar,quantity,basic_price,discount,id',
            'order_type' => 'in:asc,desc',
            'city_id' => 'exists:cities,id',
            'area_id' => 'exists:areas,id',
        ], [
            'order_by.in' => 'order_by must have value of distance or store or city or area or name_en or name_ar or quantity or basic_price or discount or id.',
            'order_by.in' => 'order_by must have value of asc or desc.'
        ]);

        $limit = ($request->has('limit')) ? $request->limit : 12;
        $search = ($request->has('search')) ? $request->search : '';
        $orderType = ($request->has('order_type')) ? $request->order_type : 'ASC';
        $orderBy =  ($request->order_by == 'distance') ? 'distance' :
                    (
                        ($request->order_by == 'store') ? 'users.name' :
                        (
                            ($request->order_by == 'city') ? 'cities.name_en' :
                            (
                                ($request->order_by == 'area') ? 'areas.name_en' :
                                (
                                    ($request->has('order_by')) ? 'items.'.$request->order_by : 'items.id'

                                )
                            )
                        )
                    );

        $lng = auth()->user()->info->lng;
        $lat = auth()->user()->info->lat;

        $followingStoresIds = auth()->user()->following()->pluck('users.id');
        do {
            $items = Item::join('branches', 'items.branch_id', '=', 'branches.id')
                ->join('users', 'users.id', '=', 'branches.store_id')
                ->join('areas', 'areas.id', '=', 'branches.area_id')
                ->join('cities', 'cities.id', '=', 'areas.city_id')
                ->select(DB::raw('items.*, ( 6367 * acos( cos( radians('.$lat.') ) * cos( radians( branches.lat ) ) * cos( radians( branches.lng ) - radians('.$lng.') ) + sin( radians('.$lat.') ) * sin( radians( branches.lat ) ) ) ) AS distance'))
                ->orderBy($orderBy, $orderType)
                ->where(function($query) use ($search){
                    $query->where('items.name_en', 'like', '%'.$search.'%' )
                    ->orWhere('items.name_ar', 'like', '%'.$search.'%');
                })
                ->whereIn('users.id', $followingStoresIds);
            if($request->has('city_id'))
                $items = $items->where('cities.id', $request->city_id);
            if($request->has('area_id'))
                $items = $items->where('areas.id', $request->area_id);
            $items = $items->paginate($limit);

            $search = mb_substr($search, 0, -1);
            if($items->total() > 0)
                break;
        } while (!empty($search));

        return $this->handlePaginateResponse(1, ItemResource::collection($items));
    }
}
