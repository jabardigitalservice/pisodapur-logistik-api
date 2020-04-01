<?php

use Illuminate\Http\Request;

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



Route::get('v1/welcome', 'API\v1\WelcomeController@index');
Route::post('v1/login', 'API\v1\UsersController@authenticate');
Route::post('v1/authenticate', 'API\v1\UsersController@authenticate');


Route::namespace('API\v1')->middleware('auth:api')->group(function () {
    // USER
    Route::get('v1/user/me', 'UsersController@me');
    Route::post('v1/user/register', 'UsersController@register');

    // AREAS
    Route::get('v1/areas/cities', 'AreasController@getCities');
    Route::get('v1/areas/subdistricts', 'AreasController@getSubDistricts');
    Route::get('v1/areas/villages', 'AreasController@getVillages');

    // PRODUCTS
    Route::get('v1/products', 'ProductsController@index');
    Route::get('v1/products/{id}', 'ProductsController@show');


    // TRANSACTIONS
    Route::prefix('v1/transaction')->group(function() {
        Route::get('/', 'TransactionController@index');
        Route::post('/', 'TransactionController@store');
        Route::get('/{id}', 'TransactionController@show');
        Route::put('/{id}', 'TransactionController@update');
        Route::delete('/{id}', 'TransactionController@destroy');
    });


    Route::apiResource('v1/recipient', 'RecipientController');
});
