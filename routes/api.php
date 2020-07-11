<?php

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

// \DB::listen(function($query) {
//     var_dump($query->sql);
// });

Route::group(['namespace' => 'Api'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::put('profile/update', 'AuthController@update');
    // Route::post('refresh', 'AuthController@refresh');
    // Route::post('me', 'AuthController@me');

    Route::group(['prefix' => 'pharmacy'], function () {
        Route::post('register', 'AuthController@registerPharmcay');

        Route::resource('cart', 'CartController');
        Route::post('cart/order', 'CartController@makeOrder');
        Route::post('cart/cancel', 'CartController@cancelOrder');

        Route::get('orders', 'OrderController@index');
        Route::get('orders/{order}', 'OrderController@show');

        Route::post('items/search', 'ItemController@search');
        Route::get('items/top100', 'ItemController@getTop100');

        Route::post('stores/{store}/follow', 'FollowController@follow');
        Route::post('stores/{store}/unfollow', 'FollowController@unfollow');
        Route::get('stores/follow/items', 'FollowController@getFollowingStoresItems');
        Route::get('stores', 'FollowController@getAllStores');
        Route::get('stores/{store}', 'FollowController@getStoreInfo');
        Route::get('stores/{store}/items', 'FollowController@getAllStoreItems');
    });

    Route::group(['prefix' => 'store'], function () {
        Route::post('register', 'AuthController@registerStore');

        Route::resource('branches', 'BranchController');
        Route::post('branches/upload', 'BranchController@upload');

        Route::resource('branches.items', 'ItemController')->shallow();
        Route::post('branches/{branch}/items/upload', 'ItemController@upload');

        Route::get('orders', 'OrderController@getStoreOrders');

        Route::get('cities', 'CityController@index');
        Route::get('cities/{city}/areas', 'CityController@getCityAreas');
    });

    Route::group(['prefix' => 'admin'], function () {
        Route::resource('cities', 'CityController');
        Route::resource('areas', 'AreaController');

        Route::get('users', 'AdminController@getAllUsers');
        Route::get('users/{user}', 'AdminController@getUserInfo');
        Route::put('users/{user}', 'AdminController@updateUserInfo');
        Route::delete('users/{user}', 'AdminController@deleteUser');

        Route::resource('stores/{store}/branches', 'AdminBranchController');
        Route::get('stores/{store}/items', 'AdminController@getStoreItems');
        Route::get('stores/{store}/upload-history', 'AdminController@getStoreUploadHistory');

        Route::get('pharmacies/{pharmacy}/orders', 'AdminController@getPharmacyOrders');
        Route::get('pharmacies/{pharmacy}/history', 'AdminController@getPharmacyHistory');

        Route::get('orders', 'AdminController@getAllOrders');
        Route::get('orders/{order}', 'OrderController@show');

        Route::get('search/history', 'AdminController@getSearchHistory');

        Route::get('items/top100', 'AdminController@getTop100Items');
    });

});

