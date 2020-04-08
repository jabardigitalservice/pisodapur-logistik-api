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
    Route::get('v1/users/me', 'UsersController@me');
    Route::post('v1/users/register', 'UsersController@register');

    // AREAS
    Route::get('v1/areas/cities', 'AreasController@getCities');
    Route::get('v1/areas/subdistricts', 'AreasController@getSubDistricts');
    Route::get('v1/areas/villages', 'AreasController@getVillages');

    // PRODUCTS
    Route::get('v1/products', 'ProductsController@index');
    Route::get('v1/products/{id}', 'ProductsController@show');


    // TRANSACTIONS
    Route::prefix('v1/transactions')->group(function() {
        Route::get('/summary', 'TransactionController@summary');
        Route::get('/export', 'TransactionController@export');
        Route::get('/{id}', 'TransactionController@show');
        Route::put('/{id}', 'TransactionController@update');
        Route::delete('/{id}', 'TransactionController@destroy');
        Route::get('/', 'TransactionController@index');
        Route::post('/', 'TransactionController@store');
    });

    Route::prefix('v1/recipients')->group(function() {
      Route::get('/', 'RecipientController@index');
      Route::get('/rdt-result-summary', 'RecipientController@summary_rdt_result');
      Route::get('/summary', 'RecipientController@summary');
      // need to be last so /summary wont be treated as city_code=summary
      Route::get('/{city_code}', 'RecipientController@show');
    });

    Route::prefix('v1/recipients-faskes')->group(function() {
      Route::get('/summary', 'RecipientFaskesController@summary');
      Route::get('/', 'RecipientFaskesController@index');
    });
});
