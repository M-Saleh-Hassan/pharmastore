<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PharmacySearchItemResource;
use App\Http\Resources\Admin\StoreOrderResource as AdminStoreOrderResource;
use App\Http\Resources\ItemResource;
use App\Http\Resources\Order\OrderBasicResource;
use App\Http\Resources\SearchItemResource;
use App\Http\Resources\StoreOrderResource;
use App\Http\Resources\StoreUploadHistoryResource;
use App\Http\Resources\UserBasicInfoResource;
use App\Models\Branch;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SearchHistory;
use App\Models\SearchHistoryResult;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth-token', 'auth-role:admin']);
    }

    public function getAllUsers(Request $request)
    {
        $this->validation($request, [
            'role' => 'in:pharmacy,store,all'
        ]);

        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';
        $orderType = ($request->has('order_type')) ? $request->order_type : 'ASC';
        $role = ($request->has('role')) ? $request->role : 'all';

        $users = User::search($search)->role($role)->paginate($limit);

        return $this->handlePaginateResponse(1, UserBasicInfoResource::collection($users));
    }

    public function getUserInfo(User $user)
    {
        return $this->handleResponse(1, new UserBasicInfoResource($user));
    }

    public function updateUserInfo(Request $request, User $user)
    {
        $this->validation($request, [
            'username' => 'unique:users,username,' . $user->id,
            'email' => 'unique:users,email,' . $user->id,
            'area_id' => 'exists:areas,id'
        ]);

        $userMappedRequest = $request->only('name', 'username', 'password', 'email', 'area_id', 'address');
        if($request->has('password'))
            $userMappedRequest['password'] = Hash::make($request->password);

        DB::beginTransaction();

        $user->update($userMappedRequest);
        $userInfoMappedRequest = $request->only('lng', 'lat', 'bio', 'delivery_details', 'mobile1', 'mobile2');

        if(empty($userInfoMappedRequest))
            return $this->handleResponse(1, new UserBasicInfoResource($user));

        $userInfoMappedRequest['user_id'] = $user->id;
        $userInfo = UserInfo::updateOrCreate(
            ['user_id' => $user->id],
            $userInfoMappedRequest
        );

        DB::commit();
        return $this->handleResponse(1, new UserBasicInfoResource($user));
    }

    public function blockUser(Request $request, User $user)
    {
        $user->is_blocked = 1;
        $user->save();
        return $this->handleResponse(1, ['message' => 'User is blocked successfully.']);
    }

    public function unblockUser(Request $request, User $user)
    {
        $user->is_blocked = 0;
        $user->save();
        return $this->handleResponse(1, ['message' => 'User is unblocked successfully.']);
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

        return $this->handlePaginateResponse(1, AdminStoreOrderResource::collection($orders));
    }

    public function showOrder(Request $request, Order $order)
    {
        $order->storeItems = $order->items;
        return $this->handleResponse(1, new AdminStoreOrderResource($order));
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

    public function getStoreItems(Request $request, User $store)
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

    public function getStoreUploadHistory(Request $request, User $store)
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

        if($request->has('branch'))
            $uploadHistories = $store->uploadHistories()->where('branch_id', $request->branch)->paginate($limit);
        else
            $uploadHistories = $store->uploadHistories()->paginate($limit);

        return $this->handlePaginateResponse(1, StoreUploadHistoryResource::collection($uploadHistories));
    }

    public function getPharmacyOrders(Request $request, User $pharmacy)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;

        // $itemsIds =  Item::where(function($query) use ($search){
        //     $query->where('name_en', 'like', '%'.$search.'%' )
        //     ->orWhere('name_ar', 'like', '%'.$search.'%');
        // })->pluck('items.id');

        $orders = Order::order()->user($pharmacy->id)->paginate($limit);
        // ->whereHas('items', function (Builder $query) use ($itemsIds) {
        //     $query->whereIn('item_id', $itemsIds);
        // })


        foreach ($orders as $order) {
            // $order->storeItems = $order->items()->whereIn('item_id', $itemsIds)->get();
            $order->storeItems = $order->items;
        }

        return $this->handlePaginateResponse(1, OrderBasicResource::collection($orders));
    }

    public function getPharmacyHistory(Request $request, User $pharmacy)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;
        // $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        // $search = ($request->has('search')) ? $request->search : '';
        // $orderType = ($request->has('order_type')) ? $request->order_type : 'DESC';

        $searchHistory = SearchHistory::where('user_id', $pharmacy->id)->paginate($limit);

        return $this->handlePaginateResponse(1, PharmacySearchItemResource::collection($searchHistory));
    }

    public function getTop100Items(Request $request)
    {
        $this->validation($request, [
            'order_by' => 'required|in:search,discount,order',
            'order_type' => 'in:asc,desc'
        ], [
            'order_by.in' => 'order_by must have value of search or discount or order.',
            'order_by.in' => 'order_by must have value of asc or desc.'
        ]);

        $orderType = ($request->has('order_type')) ? $request->order_type : 'DESC';
        $items = [];

        if($request->order_by == 'discount')
            $items = Item::orderBy('discount', $orderType)->limit(100)->get();

        if($request->order_by == 'search') {
            $history = SearchHistoryResult::select(DB::raw("COUNT('item_id') AS item_count, item_id"))
                ->has('item')
                ->groupBy('item_id')
                ->orderByDesc('item_count')
                ->limit(100)
                ->pluck('item_id');
            foreach ($history as $itemId) {
                $items[] = Item::withTrashed()->find($itemId);
            }
        }

        if($request->order_by == 'order') {
            $orderItems = OrderItem::select(DB::raw("COUNT('item_id') AS item_count, item_id"))
            ->whereHas('order', function (Builder $query) {
                $query->where('is_cart', 0)->where('is_cancelled', 0);
            })->groupBy('item_id')
            ->orderByDesc('item_count')
            ->limit(100)
            ->pluck('item_id');
            foreach ($orderItems as $itemId)
                $items[] = Item::withTrashed()->find($itemId);
        }

        return $this->handleResponse(1, ItemResource::collection($items));
    }

}
