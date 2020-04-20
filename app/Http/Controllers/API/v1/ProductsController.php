<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use JWTAuth;

use App\Http\Controllers\Controller;
use App\Product;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Product::paginate($request->input('limit',20));

        return response()->format(200, 'success', $query);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Product::findOrFail($id);
    }

    public function productUnit($id)
    {
        return Product::select('products.id', 'products.name', 'product_unit.unit_id', 'master_unit.unit')
                        ->join('product_unit', 'product_unit.product_id', '=', 'products.id')
                        ->join('master_unit', 'product_unit.unit_id', '=', 'master_unit.id')
                        ->where('products.id', $id)
                        ->get();
        
    }

}
