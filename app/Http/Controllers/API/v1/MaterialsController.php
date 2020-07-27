<?php

namespace App\Http\Controllers\API\v1;

use App\Material;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Usage;
use App\SohLocation;
use App\WmsJabarMaterial;

class MaterialsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [];
        $sohLocation = SohLocation::all();
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
        $dataFinal = [];
        foreach ($data as $material_id => $material) {
            $dataFinal[] = $material;
        }
        $data = $dataFinal;
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
