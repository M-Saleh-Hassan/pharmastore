<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\ItemsImport;
use App\Models\Branch;
use App\Models\Item;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth-token', 'auth-role:store']);
    }

    public function index(Request $request, Branch $branch)
    {
        if(!auth()->user()->branches->contains($branch->id))
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));

        $limit = ($request->has('limit')) ? $request->limit : 12;
        $orderBy = ($request->has('order_by')) ? $request->order_by : 'id';
        $search = ($request->has('search')) ? $request->search : '';

        $items = Item::orderBy($orderBy)
            ->where('branch_id', $branch->id)
            ->where(function($query) use ($search){
                $query->where('name_en', 'like', '%'.$search.'%' )
                ->orWhere('name_ar', 'like', '%'.$search.'%');
            })->paginate($limit);

        return $this->handlePaginateResponse(1, $items);
    }

    public function store(Request $request, Branch $branch)
    {
        if(!auth()->user()->branches->contains($branch->id))
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));

        $this->validation($request, [
            'name_en' => 'required',
            'name_ar' => 'required',
            'quantity' => 'required|int|min:0',
            'basic_price' => 'required',
            'discount' => 'required|int|min:0'
        ]);

        $itemMappedRequest = $request->all();
        $itemMappedRequest['branch_id'] = $branch->id;
        $item = Item::create($itemMappedRequest);

        return $this->handleResponse(1, $item);
    }


    public function show(Item $item)
    {
        if($item->branch->store->id != auth()->user()->id)
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));

        return $this->handleResponse(1, $item);
    }


    public function update(Request $request, Item $item)
    {
        if($item->branch->store->id != auth()->user()->id)
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));

        if($request->has('branch_id'))
            if(!auth()->user()->branches->contains($request->branch_id))
                throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));

        $this->validation($request, [
            'quantity' => 'required|int|min:0',
            'discount' => 'required|int|min:0'
        ]);

        $item->update($request->all());
        return $this->handleResponse(1, $item);
    }

    public function destroy(Item $item)
    {
        if($item->branch->store->id != auth()->user()->id)
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));

        $item->delete();
        return $this->handleResponse(1, ['message' => 'Item is deleted successfully.']);
    }

    public function upload(Request $request,Branch $branch)
    {
        if($branch->store_id != auth()->user()->id)
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized to perform this operation']], 401));

        $this->validation($request, [
            'file' => 'required|max:10000',
        ]);
        $extensions = array("xls","xlsx","xlm","xla","xlc","xlt","xlw");
        $extension = $request->file('file')->getClientOriginalExtension();
        if(!in_array($extension,$extensions))
            throw new HttpResponseException(response()->json(['success'=> 0, 'errors' => ['message' => 'The file must be a file of type: xlsx, xls, xlm']], 422));

        Excel::import(new ItemsImport($branch), request()->file('file'));
        return $this->handleResponse(1, ['message' => 'Items are imported successfully.']);
    }
}
