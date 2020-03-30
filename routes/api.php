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


Route::namespace('API\v1')->middleware('auth:api')->group(function () {
  Route::get('/user', function (Request $request) {
      return $request->user();
  });

  Route::prefix('transaction')->group(function() {
    Route::get('/', 'TransactionController@index');
    Route::post('/', 'TransactionController@store');
    Route::get('/{id}', 'TransactionController@show');
    Route::put('/{id}', 'TransactionController@update');
    Route::delete('/{id}', 'TransactionController@destroy');
  });
});
