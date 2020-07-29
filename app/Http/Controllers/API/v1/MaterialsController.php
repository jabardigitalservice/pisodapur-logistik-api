<?php

namespace App\Http\Controllers\API\v1;

use App\Material;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Usage;
use App\SohLocation;
use App\WmsJabarMaterial;
use App\Product;

class MaterialsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = [];
        $material_group = '';
        $status = '';
        $product = [];
        $sohLocation = SohLocation::all();
        $condition = false;
        if ($request->filled('id') && $request->filled('status')) {
            $product = $request->input('status') == 'approved' ? Product::find($request->input('id')) : $product;
            $material_group = $product ? $product->material_group : $material_group;
            $condition = true;
        }

        foreach ($sohLocation as $val) {
            $materials = Usage::getLogisticStockByLocation($val['location_id']);
            foreach ($materials as $material) {
                if (!isset($data[$material->material_id])) {
                    if ($material->stock_ok > 0) {
                        $data[$material->material_id] = [
                            'id' => $material->material_id,
                            'name' => $material->material_name,
                            'matg_id' => $material->matg_id,
                            'UoM' => $material->UoM,
                            'stock_ok' => $material->stock_ok
                        ];
                    } 
                } else { 
                    $data[$material->material_id]['stock_ok'] += $material->stock_ok;
                }
            }
        }

        // Finalisasi data yang akan dilempar
        $data = array_values($data);
        if ($condition) {
            foreach ($data as $key => $val) {
                if ($val['matg_id'] != $material_group) {
                    unset($data[$key]);
                } 
            }
        }
        return response()->format(200, 'success', $data);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function productUnitList($id)
    {
        $data = WmsJabarMaterial::select('id', 'UoM as name')->where('material_id', $id)->get();
        return response()->format(200, 'success', $data);
    }
}
