<?php

namespace App\Http\Controllers\API\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Usage;
use App\Product;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $param = '';
        $product = [];
        $api = '';
        
        if ($request->filled('id')) {            
            $param = '{"material_id":"' . $request->input('id') . '"}';
            $api = '/api/soh_fmaterial';
        }

        $retApi = Usage::getLogisticStock($param, $api);
        
        //grouping data berdasarkan soh_location-nya
        $data = [];      
        if (is_array($retApi) || is_object($retApi)) {  
            foreach ($retApi as $val) {
                if (!isset($data[$val->material_id.'-'.$val->soh_location])) {
                    $data[$val->material_id.'-'.$val->soh_location]['soh_location'] = $val->soh_location;
                    $data[$val->material_id.'-'.$val->soh_location]['soh_location_name'] = $val->soh_location_name;
                    $data[$val->material_id.'-'.$val->soh_location]['UoM'] = $val->UoM;
                    $data[$val->material_id.'-'.$val->soh_location]['matg_id'] = $val->matg_id;
                    $data[$val->material_id.'-'.$val->soh_location]['material_id'] = $val->material_id;
                    $data[$val->material_id.'-'.$val->soh_location]['matg_name'] = $val->material_name;
                    $data[$val->material_id.'-'.$val->soh_location]['stock_ok'] = $val->stock_ok;
                    $data[$val->material_id.'-'.$val->soh_location]['stock_nok'] = $val->stock_nok;
                } else { 
                    $data[$val->material_id.'-'.$val->soh_location]['stock_ok'] += $val->stock_ok;
                    $data[$val->material_id.'-'.$val->soh_location]['stock_nok'] += $val->stock_nok;
                }
            }
        }

        // Finalisasi data yang akan dilempar
        $dataFinal = [];
        foreach ($data as $loc => $val) {
            if ($val['stock_ok'] > 0 || $val['stock_nok'] > 0) {
                $dataFinal[] = $val;
            }
        }

        return response()->format(200, true, $dataFinal);
    }
}
