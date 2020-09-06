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
        if ($condition) {
            foreach ($data as $key => $val) {
                if ($val['matg_id'] != $material_group) {
                    unset($data[$key]);
                } 
            }
        }
        $data = array_values($data);
        return response()->format(200, 'success', $data);
    }
    
    /**
     * Display a listing of the resource.
     * if did not exists in our database, system will update material list
     *
     * @return \Illuminate\Http\Response
     */
    public function productUnitList($id)
    {
        $exists = WmsJabarMaterial::select('id', 'UoM as name')->where('material_id', $id)->exists();
        if ($exists) {
            $data = WmsJabarMaterial::select('id', 'UoM as name')->where('material_id', $id)->get();
            return response()->format(200, 'success', $data);
        } else {
            $this->integrateMaterial();
            $data = WmsJabarMaterial::select('id', 'UoM as name')->where('material_id', $id)->get();
            return response()->format(200, 'success', $data);
        }
    }

    /**
     * integrateMaterial function
     *
     * untuk menyimpan dan mengupdate seluruh data barang yang berasal dari PosLog agar tersimpan di database. 
     * 
     * @return void
     */
    public function integrateMaterial()
    {
        $materials = Usage::getMaterialPosLog();
        WmsJabarMaterial::truncate();

        $data = [];
        foreach ($materials as $val) {
            $item = [
                'material_id' => $val->material_id,
                'uom' => $val->uom,
                'material_name' => $val->material_name,
                'matg_id' => $val->matg_id,
                'matgsub_id' => $val->matgsub_id,
                'material_desc' => $val->material_desc ? $val->material_desc : '-',
                'donatur_id' => $val->donatur_id,
                'donatur_name' => $val->donatur_name,
            ];
            $data[] = $item;
        }
        WmsJabarMaterial::insert($data);
    }
}
