<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SearchItemResource;
use App\Http\Resources\StoreOrderResource;
use App\Http\Resources\StoreUploadHistoryResource;
use App\Http\Resources\UserBasicInfoResource;
use App\Models\Branch;
use App\Models\Item;
use App\Models\Order;
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
            'email' => 'unique:users,email,' . $user->id
        ]);

        $userMappedRequest = $request->only('name', 'username', 'password', 'email');
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

    public function getStoreItems(Request $request, User $store)
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

}
