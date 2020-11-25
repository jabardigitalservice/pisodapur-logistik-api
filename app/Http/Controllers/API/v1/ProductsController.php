<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Product;
use App\Applicant;
use App\Needs;
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
            $query = Product::where('products.is_imported', false)
            ->where('products.material_group_status', 1)
            ->where(function ($query) use ($request) {
                if ($request->filled('limit')) {
                    $query->paginate($request->input('limit'));
                }
    
                if ($request->filled('name')) {
                    $query->where('products.name', 'LIKE', "%{$request->input('name')}%");
                }
    
                if ($request->filled('user_filter')) {
                    $query->where('products.user_filter', '=', $request->input('user_filter'));
                }
            })
            ->orderBy('products.sort', 'ASC')->orderBy('products.name', 'ASC');
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
        return Product::where('id', $id)->firstOrFail();
    }

    public function productUnit($id)
    {
        $data = Product::select(
                'products.id', 
                'products.name',  
                DB::raw('IFNULL(product_unit.unit_id, 1) as unit_id'),
                DB::raw('IFNULL(master_unit.unit, "PCS") as unit')
            )
            ->leftJoin('product_unit', 'product_unit.product_id', '=', 'products.id')
            ->leftJoin('master_unit', function ($join) {
                $join->on('product_unit.unit_id', '=', 'master_unit.id')
                    ->where('master_unit.is_imported', false);
            })
            ->where('products.id', $id) 
            ->get();
            
        return $data;
    }

    public function productRequest(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->input('start_date') . ' 00:00:00' : '2020-01-01 00:00:00';
        $endDate = $request->filled('end_date') ? $request->input('end_date') . ' 23:59:59' : date('Y-m-d H:i:s');

        try {
            $query = Product::select(
                'products.id', 
                'products.name',
                'needs.unit',
                DB::raw('SUM(needs.quantity) as total_request')
            )
            ->leftJoin('needs', function ($join) use ($startDate, $endDate) {
                $join->on('needs.product_id', '=', 'products.id')
                ->join('applicants', function($join) use ($startDate, $endDate) {
                    $join->on('needs.agency_id', '=', 'applicants.agency_id');
                });
            })    
            ->where('applicants.verification_status', Applicant::STATUS_VERIFIED)
            ->where('applicants.is_deleted', '!=', 1)
            ->whereBetween('applicants.created_at', [$startDate, $endDate]) 
            ->where('products.material_group_status', 1)
            ->orderBy('total_request', $request->input('sort', 'desc'))            
            ->groupBy('products.id', 'products.name', 'needs.unit');
            if ($request->filled('limit')) {
                $data = $query->paginate($request->input('limit'));
            } else {
                $data = [
                    'data' => $query->get(),
                    'total' => $query->get()->count()
                ];
            }

            $response = response()->format(200, 'success', $data);
        } catch (\Exception $exception) {
            $response = response()->format(400, $exception->getMessage());
        }
        return $response;
    }

    /**
     * productTopRequest function
     * 
     * to get top 1 requested product
     * 
     * @param Request $request
     * @return void
     */
    public function productTopRequest(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->input('start_date') . ' 00:00:00' : '2020-01-01 00:00:00';
        $endDate = $request->filled('end_date') ? $request->input('end_date') . ' 23:59:59' : date('Y-m-d H:i:s');

        $data = [
            'total_items' => $this->totalItems($startDate, $endDate),
            'total_max' => $this->totalMax($startDate, $endDate)
        ];
        return response()->format(200, 'success', $data);
    }

    public function totalItems($startDate, $endDate)
    {
        $totalItems = Needs::join('applicants', 'needs.agency_id', '=', 'applicants.agency_id') 
        ->where('applicants.verification_status', Applicant::STATUS_VERIFIED)
        ->where('applicants.is_deleted', '!=', 1)
        ->whereBetween('applicants.created_at', [$startDate, $endDate]) 
        ->sum('quantity');
        return $totalItems;
    }

    public function totalMax($startDate, $endDate)
    {
        $sort = ['total DESC, ', 'products.name ASC'];
        $totalMax = Product::select(
            'products.id', 
            'products.name',
            DB::raw('SUM(needs.quantity) as total'),
            'needs.unit',
            'products.category'
        )
        ->leftJoin('needs', function ($join) use ($startDate, $endDate) {
            $join->on('needs.product_id', '=', 'products.id')
            ->join('applicants', function($join) use ($startDate, $endDate) {
                $join->on('needs.agency_id', '=', 'applicants.agency_id');
            });
        })
        ->where('applicants.verification_status', Applicant::STATUS_VERIFIED)
        ->where('applicants.is_deleted', '!=', 1)
        ->whereBetween('applicants.created_at', [$startDate, $endDate])
        ->where('products.material_group_status', 1)
        ->orderByRaw(implode($sort))
        ->groupBy('products.id', 'products.name', 'needs.unit', 'products.category')->first();

        return $totalMax;
    }
}
