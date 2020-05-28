<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use JWTAuth;

use App\Http\Controllers\Controller;
use App\Product;
use DB;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Product::orderBy('products.name', 'ASC');
            if ($request->filled('limit')) {
                $query->paginate($request->input('limit'));
            }

            if ($request->filled('name')) {
                $query->where('products.name', 'LIKE', "%{$request->input('name')}%");
            }

            $query->where('products.is_imported', false);
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return response()->format(200, 'success', $query->get());
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
            ->join('master_unit', function ($join) {
                $join->on('product_unit.unit_id', '=', 'master_unit.id')
                    ->where('master_unit.is_imported', false);
            })
            ->where('products.id', $id)
            ->get();
    }

    public function productRequest(Request $request)
    {
        try {
            $query = Product::select('products.*', DB::raw('SUM(REPLACE(needs.quantity, ".", "")) as total_request'))
            ->leftJoin('needs', function($join) {
                $join->on('needs.product_id', '=', 'products.id');
            })
            ->leftJoin('applicants', function($join) {
                $join->on('needs.agency_id', '=', 'applicants.agency_id');
            })
            ->leftJoin('product_unit', function($join) {
                $join->on('product_unit.product_id', '=', 'products.id');
            })
            ->leftJoin('master_unit', function($join) {
                $join->on('product_unit.unit_id', '=', 'master_unit.id');
            })
            ->where('applicants.verification_status', 'verified')
            ->groupBy('products.id');

            if ($request->filled('sort')) {
                $query->orderBy('total_request', $request->input('sort'));
            } 

            if ($request->filled('limit')) {
                $data = $query->paginate($request->input('limit'));
            } else {
                $data = [
                    'data' => $query->get(),
                    'total' => $query->get()->count()
                ];
            }
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return response()->format(200, 'success', $data);
    }
}
