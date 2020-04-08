<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /*
     * Custom method to create pagination from a normal array
     * ref : https://arjunphp.com/laravel-5-pagination-array/
     *
     * @return Paginator Object
     */
    public function paginateArray($obj, $request)
    {
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($obj); // Create a new Laravel collection from the array data
        $perPage = $request->input('limit',20); // Define how many items we want to be visible in each page
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all(); // Slice the collection to get the items to display in current page
        $data = new \Illuminate\Pagination\LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage); // Create our paginator and pass it to the view
        $data->setPath($request->url()); // set url path for generted links

        return $data;
    }
}
