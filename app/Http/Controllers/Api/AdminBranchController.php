<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class AdminBranchController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth-token', 'auth-role:admin']);
    }

    public function index(Request $request, User $store)
    {
        if(!$store->isStore())
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'id passed isn\'t a store']], 401));

        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';

        $branches = Branch::orderBy($orderBy)->where('store_id', $store->id)->where('name', 'like', '%'.$search.'%' )->paginate($limit);

        return $this->handlePaginateResponse(1, $branches);
    }

    public function store(Request $request, User $store)
    {
        if(!$store->isStore())
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'id passed isn\'t a store']], 401));

        $this->validation($request, [
            'area_id' => 'required|exists:areas,id',
            'lng' => 'required',
            'lat' => 'required',
            'mobile' => 'required',
            'working_hours_from' => 'required',
            'working_hours_to' => 'required',
            'working_days_from' => 'required',
            'working_days_to' => 'required',
        ]);
        $branchMappedRequest = $request->all();
        $branchMappedRequest['store_id'] = $store->id;
        $branch = Branch::create($branchMappedRequest);

        return $this->handleResponse(1, $branch);
    }


    public function show(User $store, Branch $branch)
    {
        if(!$store->isStore())
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'id passed isn\'t a store']], 401));

        if($branch->store_id != $store->id)
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));

        return $this->handleResponse(1, $branch);
    }


    public function update(Request $request, User $store, Branch $branch)
    {
        if(!$store->isStore())
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'id passed isn\'t a store']], 401));

        if($branch->store_id != $store->id)
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));

        $branch->update($request->all());
        return $this->handleResponse(1, $branch);
    }

    public function destroy(User $store, Branch $branch)
    {
        if(!$store->isStore())
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'id passed isn\'t a store']], 401));

        if($branch->store_id != $store->id)
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));
        $branch->items()->delete();
        $branch->delete();
        return $this->handleResponse(1, ['message' => 'Branch is deleted successfully.']);
    }
}
