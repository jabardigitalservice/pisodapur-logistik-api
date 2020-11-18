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
Route::get('v1/ping', function() {
  $response = Response::make(gethostname(), 200);
  $response->header('Content-Type', 'text/plain');
  return $response;
});

// Landing Page Registration
Route::namespace('API\v1')->group(function () {
  Route::prefix('v1/landing-page-registration')->group(function () {
    //Landing Page Registration
    Route::post('/agency', 'AgencyController@store');
    Route::post('/applicant', 'ApplicantController@store');
    Route::post('/needs', 'NeedsController@store');
    Route::post('/letter', 'LetterController@store');

    // AREAS, for public
    Route::get('/areas/cities', 'AreasController@getCities');
    Route::get('/areas/subdistricts', 'AreasController@getSubDistricts');
    Route::get('/areas/villages', 'AreasController@getVillages');

    Route::get('/products', 'ProductsController@index');
    Route::get('/product-unit/{id}', 'ProductsController@productUnit');

    //Tracking Application
    Route::get('/tracking', 'LogisticRequestController@track');
    Route::get('/tracking/{id}', 'LogisticRequestController@trackDetail');
  });
  
    Route::post('v1/logistic-request', 'LogisticRequestController@store');
    Route::apiResource('v1/master-faskes', 'MasterFaskesController');
    Route::apiResource('v1/master-faskes-type', 'MasterFaskesTypeController');
    Route::post('v1/master-faskes', 'MasterFaskesController@store');
    Route::get('v1/master-faskes/{id}', 'MasterFaskesController@show');
    Route::post('v1/verify-master-faskes/{id}', 'MasterFaskesController@verify');
});

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
    Route::get('v1/products-total-request', 'ProductsController@productRequest');
    Route::get('v1/products-top-request', 'ProductsController@productTopRequest');

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
      Route::get('/export', 'RecipientFaskesController@export');
      Route::get('/', 'RecipientFaskesController@index');
    });

    Route::get('v1/logistic-request', 'LogisticRequestController@index');
    Route::get('v1/logistic-request/{id}', 'LogisticRequestController@show');
    Route::post('v1/logistic-request/verification', 'LogisticRequestController@verification');
    Route::get('v1/logistic-request/need/list', 'LogisticRequestController@listNeed');
    Route::post('v1/logistic-request/import', 'LogisticRequestController@import');
    Route::post('v1/logistic-request/realization', 'LogisticRealizationItemController@store');
    Route::get('v1/logistic-request/cities/total-request', 'AreasController@getCitiesTotalRequest');
    Route::get('v1/logistic-request/data/export', 'ExportLogisticRequestController@export');
    Route::post('v1/logistic-request-non-public', 'LogisticRequestController@store')->name('non-public');
    Route::post('v1/logistic-request/approval', 'LogisticRequestController@approval');
    Route::post('v1/logistic-request/final', 'LogisticRequestController@final');
    Route::post('v1/logistic-request/stock-checking', 'LogisticRequestController@stockCheking');
    Route::post('v1/logistic-request/letter/{id}', 'LogisticRequestController@uploadLetter');
    Route::post('v1/logistic-request/identity/{id}', 'LogisticRequestController@uploadApplicantFile');
    Route::post('v1/logistic-request/urgency', 'LogisticRequestController@urgencyChange');
    Route::put('v1/logistic-request/{id}', 'LogisticRequestController@update');
    Route::post('v1/logistic-request/applicant-letter/{id}', 'LogisticRequestController@update');
    Route::post('v1/logistic-request/applicant-identity/{id}', 'LogisticRequestController@update');

    // Logistic Realization Items by Admin
    Route::get('v1/logistic-admin-realization', 'LogisticRealizationItemController@list');
    Route::post('v1/logistic-admin-realization', 'LogisticRealizationItemController@add');
    Route::put('v1/logistic-admin-realization/{id}', 'LogisticRealizationItemController@update');
    Route::delete('v1/logistic-admin-realization/{id}', 'LogisticRealizationItemController@destroy');
    
    // STOCK
    Route::get('v1/stock', 'StockController@index');

    // Outgoing Letter Management
    Route::get('v1/outgoing-letter', 'OutgoingLetterController@index');
    Route::get('v1/outgoing-letter-print/{id}', 'OutgoingLetterController@print');
    Route::get('v1/outgoing-letter/{id}', 'OutgoingLetterController@show');
    Route::post('v1/outgoing-letter', 'OutgoingLetterController@store');
    Route::post('v1/outgoing-letter/upload', 'OutgoingLetterController@upload');
    Route::put('v1/outgoing-letter/{id}', 'OutgoingLetterController@update');

    //Request Letter Management
    Route::get('v1/application-letter', 'RequestLetterController@index');
    Route::get('v1/application-letter/search-by-letter-number', 'RequestLetterController@searchByLetterNumber');
    Route::get('v1/application-letter/{id}', 'RequestLetterController@show');
    Route::post('v1/application-letter', 'RequestLetterController@store');
    Route::put('v1/application-letter/{id}', 'RequestLetterController@update');
    Route::delete('v1/application-letter/{id}', 'RequestLetterController@destroy');
    
    //Logistic Realization Integrate with PosLog
    Route::get('v1/logistic-realization/products', 'StockController@index');
    Route::get('v1/logistic-realization/product-units/{id}', 'StockController@productUnitList');
    Route::get('v1/logistic-realization/sync', 'LogisticRealizationItemController@integrateMaterial');
    
    //Incoming Letter Management
    Route::get('v1/incoming-letter', 'IncomingLetterController@index');
    Route::get('v1/incoming-letter/{id}', 'IncomingLetterController@show');
    
    //Dashboard
    Route::get('v1/faskes-type-total-request', 'MasterFaskesTypeController@masterFaskesTypeRequest');
    Route::get('v1/faskes-type-top-request', 'MasterFaskesTypeController@masterFaskesTypeTopRequest');
    Route::get('v1/logistic-request-summary', 'LogisticRequestController@requestSummary');

    //Notification via Whatsapp
    Route::post('v1/notify', 'ChangeStatusNotifyController@sendNotification');
});

//Route for Another App that want integrate data
Route::namespace('API\v1')->middleware('auth-key')->group(function () {
  Route::get('v1/products-total-request', 'ProductsController@productRequest');
  Route::get('v1/products-top-request', 'ProductsController@productTopRequest');
  Route::get('v1/faskes-type-total-request', 'MasterFaskesTypeController@masterFaskesTypeRequest');
  Route::get('v1/faskes-type-top-request', 'MasterFaskesTypeController@masterFaskesTypeTopRequest');
  Route::get('v1/logistic-request-summary', 'LogisticRequestController@requestSummary');
  Route::get('v1/logistic-request/cities/total-request', 'AreasController@getCitiesTotalRequest');
});