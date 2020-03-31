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


Route::post('v1/register', 'API\v1\UsersController@register');
Route::post('v1/login', 'API\v1\UsersController@authenticate');
Route::post('v1/authenticate', 'API\v1\UsersController@authenticate');


Route::namespace('API\v1')->middleware('auth:api')->group(function () {
    // Route::get('/user', function (Request $request) {
    //     return $request->user();
    // });

    Route::prefix('v1/transaction')->group(function() {
        Route::get('/', 'TransactionController@index');
        Route::post('/', 'TransactionController@store');
        Route::get('/{id}', 'TransactionController@show');
        Route::put('/{id}', 'TransactionController@update');
        Route::delete('/{id}', 'TransactionController@destroy');
    });

});
