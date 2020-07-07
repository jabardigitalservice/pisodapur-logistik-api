<?php

namespace App\Http\Controllers\API\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Usage;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $param = '';
        $api = '';
        
        if ($request->filled('material_group')) {
            $param = '{"material_group":"' . $request->input('material_group') . '"}';
            $api = '/api/soh_fmaterialgroup';
        }

        $retApi = Usage::getLogisticStock($param, $api);
        
        //grouping data berdasarkan soh_location-nya
        $data = [];      
        if (is_array($retApi) || is_object($retApi)) {  
            foreach ($retApi as $val) {
                if (!isset($data[$val->soh_location])) {
                    $data[$val->soh_location]['soh_location'] = $val->soh_location;
                    $data[$val->soh_location]['soh_location_name'] = $val->soh_location_name;
                    $data[$val->soh_location]['UoM'] = $val->UoM;
                    $data[$val->soh_location]['matg_id'] = $val->matg_id;
                    $data[$val->soh_location]['matg_name'] = $val->matg_id;
                    $data[$val->soh_location]['stock_ok'] = $val->stock_ok;
                    $data[$val->soh_location]['stock_nok'] = $val->stock_nok;
                } else { 
                    $data[$val->soh_location]['stock_ok'] += $val->stock_ok;
                    $data[$val->soh_location]['stock_nok'] += $val->stock_nok;
                }
            }
        }

        $dataFinal = [];
        // Finalisasi data yang akan dilempar
        foreach ($data as $loc => $val) {
            $dataFinal[] = $val;
        }

        return response()->format(200, true, $dataFinal);
    }
}
