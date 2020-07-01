<?php

namespace App\Http\Controllers\Api;

use App\Events\ItemSearched;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemCollection;
use App\Imports\ItemsImport;
use App\Models\Branch;
use App\Models\Item;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth-token');
        $this->middleware('auth-role:store')->except('search');
        $this->middleware('auth-role:pharmacy')->only('search');
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
            'quantity' => 'required|min:0',
            'basic_price' => 'required',
            'discount' => 'required|min:0'
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
            'quantity' => 'required|min:0',
            'discount' => 'required|min:0'
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

        $currentItems = $branch->items;
        foreach ($currentItems as $item) {
            $orderItems = $item->orderItems;
            foreach ($orderItems as $orderItem) {
                if($orderItem->order->is_cart)
                    $orderItem->delete();
            }
        }
        $branch->items()->delete();

        Excel::import(new ItemsImport($branch), request()->file('file'));
        return $this->handleResponse(1, ['message' => 'Items are imported successfully.']);
    }

    /*
     *Pharmacy Part
    */
    public function search(Request $request)
    {
        $this->validation($request, [
            'search' => 'required',
            'order_by' => 'in:distance,store,city,area,name_en,name_ar,quantity,basic_price,discount,id',
            'order_type' => 'in:asc,desc'
        ], [
            'order_by.in' => 'order_by must have value of distance or store or city or area or name_en or name_ar or quantity or basic_price or discount or id.',
            'order_by.in' => 'order_by must have value of asc or desc.'
        ]);

        $lng = auth()->user()->info->lng;
        $lat = auth()->user()->info->lat;

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
                })->paginate($limit);
            $search = substr($search, 0, -1);
            if($items->total() > 0)
                break;
        } while (!empty($search));

        $items->count = $items->total();

        if($items->count)
            event(new ItemSearched($items, $search, auth()->user()->id));

        return $this->handleResponse(1, new ItemCollection($items));
    }
}
