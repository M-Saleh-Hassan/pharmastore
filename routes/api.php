<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['namespace' => 'Api'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::put('profile/update', 'AuthController@update');
    // Route::post('refresh', 'AuthController@refresh');
    // Route::post('me', 'AuthController@me');

    Route::group(['prefix' => 'pharmacy'], function () {
        Route::post('register', 'AuthController@registerPharmcay');
    });

    Route::group(['prefix' => 'store'], function () {
        Route::post('register', 'AuthController@registerStore');

        Route::resource('branches', 'BranchController');
        Route::post('branches/upload', 'BranchController@upload');

        Route::resource('branches.items', 'ItemController')->shallow();
        Route::post('branches/{branch}/items/upload', 'ItemController@upload');


    });

    Route::group(['prefix' => 'admin'], function () {
        Route::resource('cities', 'CityController');
        Route::resource('areas', 'AreaController');
    });

});

