<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\BranchesImport;
use App\Models\Branch;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth-token', 'auth-role:store']);
    }

    public function index(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';

        $branches = Branch::orderBy($orderBy)->where('name', 'like', '%'.$search.'%' )->paginate($limit);

        return $this->handlePaginateResponse(1, $branches);
    }

    public function store(Request $request)
    {
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
        $branchMappedRequest['store_id'] = auth()->user()->id;
        $branch = Branch::create($branchMappedRequest);

        return $this->handleResponse(1, $branch);
    }


    public function show(Branch $branch)
    {
        if($branch->store_id != auth()->user()->id)
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));

        return $this->handleResponse(1, $branch);
    }


    public function update(Request $request, Branch $branch)
    {
        if($branch->store_id != auth()->user()->id)
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));

        $branch->update($request->all());
        return $this->handleResponse(1, $branch);
    }

    public function destroy(Branch $branch)
    {
        if($branch->store_id != auth()->user()->id)
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));

        $branch->delete();
        return $this->handleResponse(1, ['message' => 'Branch is deleted successfully.']);
    }

    public function upload(Request $request)
    {
        Excel::import(new BranchesImport,request()->file('file'));
        return $this->handleResponse(1, ['message' => 'Branches are imported successfully.']);
    }
}
