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
        $baseApi = '';
        $api = '';
        if ($request->filled('poslog_id')) {
            $param = '{"material_id":"' . $request->input('poslog_id') . '"}';
            $api = '/api/soh_fmaterial';
        } else {
            $product = Product::findOrFail($request->input('id'));
            $materialGroupId = $product->material_group;
            $baseApi = $product->api;
            $param = ($baseApi === 'DASHBOARD_PIKOBAR_API_BASE_URL') ? '{"matg_id":"' . $materialGroupId . '"}' : '{"material_group":"' . $materialGroupId . '"}';
            $api = ($baseApi === 'DASHBOARD_PIKOBAR_API_BASE_URL') ? '/master/soh?where=' . $param : '/api/soh_fmaterialgroup';
        }
        $retApi = Usage::getLogisticStock($param, $api, $baseApi);
        //grouping data berdasarkan soh_location-nya
        $data = [];
        if (is_array($retApi) || is_object($retApi)) {
            foreach ($retApi as $val) {
                if (!isset($data[$val->material_id.'-'.$val->soh_location])) {
                    $data[$val->material_id.'-'.$val->soh_location]['soh_location'] = $val->soh_location;
                    $data[$val->material_id.'-'.$val->soh_location]['soh_location_name'] = $val->soh_location_name;
                    $data[$val->material_id.'-'.$val->soh_location]['UoM'] = isset($val->UoM) ? $val->UoM : $val->uom;
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
