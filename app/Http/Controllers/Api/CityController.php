<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth-token');
        $this->middleware('auth-role:admin')->except('index');
    }

    public function index(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->limit : 12;
        $cities = City::orderBy('name_en')->paginate($limit);

        return $this->handlePaginateResponse(1, $cities);
    }

    public function store(Request $request)
    {
        $this->validation($request, [
            'name_en' => 'required',
            'name_ar' => 'required'
        ]);

        $city = City::create($request->all());

        return $this->handleResponse(1, $city);
    }


    public function show(City $city)
    {
        return $this->handleResponse(1, $city);
    }


    public function update(Request $request, City $city)
    {
        $city->update($request->all());
        return $this->handleResponse(1, $city);
    }

    public function destroy(City $city)
    {
        $city->delete();
        return $this->handleResponse(1, ['message' => 'city is deleted successfully.']);
    }
}
