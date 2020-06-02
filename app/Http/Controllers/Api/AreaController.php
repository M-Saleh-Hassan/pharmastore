<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth-token');
        $this->middleware('auth-role:admin')->except('index');
    }

    public function index(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;
        $areas = Area::orderBy('name_en')->paginate($limit);

        return $this->handlePaginateResponse(1, $areas);
    }

    public function store(Request $request)
    {
        $this->validation($request, [
            'city_id' => 'required|exists:cities,id',
            'name_en' => 'required',
            'name_ar' => 'required'
        ]);

        $area = Area::create($request->all());

        return $this->handleResponse(1, $area);
    }


    public function show(Area $area)
    {
        return $this->handleResponse(1, $area);
    }


    public function update(Request $request, Area $area)
    {
        $area->update($request->all());
        return $this->handleResponse(1, $area);
    }

    public function destroy(Area $area)
    {
        $area->delete();
        return $this->handleResponse(1, ['message' => 'Area is deleted successfully.']);
    }
}
