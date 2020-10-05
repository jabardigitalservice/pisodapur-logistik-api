<?php

namespace App\Http\Controllers\API\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Usage;
use App\Product;
use App\PoslogProduct;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $param = '';
        $baseApi = 'WMS_JABAR_BASE_URL';
        $api = '/api/soh_fmaterialgroup';
        $fieldPoslog = 'matg_id';
        $valuePoslog = '';
        $outDate = false;
        $dataFinal = [];
        $materialName = false;
        try {
            if ($request->filled('poslog_id')) {
                $valuePoslog = $request->input('poslog_id');
                $baseApi = 'DASHBOARD_PIKOBAR_API_BASE_URL';
                $param = '?where={"matg_id":"' . $request->input('poslog_id') . '"}';
                $api = '/master/inbound_detail';
            } else {
                $product = Product::findOrFail($request->input('id'));
                $baseApi = $product->api;
                $valuePoslog = $product->material_group;
                $param = ($baseApi === 'DASHBOARD_PIKOBAR_API_BASE_URL') ? '?where={"matg_id":"' . $product->material_group . '"}' : '{"material_group":"' . $product->material_group . '"}';
                if (strpos($product->name, 'VTM') !== false) {
                    $param = '?search=VTM';
                    $materialName = 'VTM';
                }
                $api = ($baseApi === 'DASHBOARD_PIKOBAR_API_BASE_URL') ? '/master/inbound_detail' . $param : $api;
            }
            if ($baseApi !== 'DASHBOARD_PIKOBAR_API_BASE_URL') {
                $this->syncPoslogData($param, $api, $baseApi);
                $outDate = true;
            } else if ($this->poslogItemOutdated($fieldPoslog, $valuePoslog, $baseApi)) {
                $this->syncPoslogData($param, $api, $baseApi);
            }
            $dataFinal = $this->getPoslogItem($valuePoslog, $materialName);
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
        return response()->format(200, $outDate, $dataFinal);
    }

    public function poslogItemOutdated($field, $value, $baseApi)
    {
        $now = date('Y-m-d H:i:s');
        $firstSyncTime = date('Y-m-d') . ' 02:00:00'; //UTC Timezone for 08:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
        $secondSyncTime = date('Y-m-d') . ' 06:00:00'; //UTC Timezone for 12:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
        $thirdSyncTime = date('Y-m-d') . ' 10:00:00'; //UTC Timezone for 16:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
        $result = false;

        try {
            $poslogProduct = PoslogProduct::where($field, '=', $value)->where('soh_location', '=', 'GUDANG LABKES')->orderBy('updated_at','desc')->firstOrFail();
            if ($now > $thirdSyncTime && $poslogProduct->updated_at < $thirdSyncTime) {
                $result = true;
            } else if ($now > $secondSyncTime && $poslogProduct->updated_at < $secondSyncTime) {
                $result = true;
            } else if ($now > $firstSyncTime && $poslogProduct->updated_at < $firstSyncTime) {
                $result = true;
            }
        } catch (\Exception $exception) {
            $result = true;
        }

        return $result;
    }

    public function syncPoslogData($param, $api, $baseApi)
    {
        $retApi = Usage::getLogisticStock($param, $api, $baseApi);
        //grouping data berdasarkan soh_location-nya
        $data = [];
        $matgId = '';
        if (is_array($retApi) || is_object($retApi)) {
            foreach ($retApi as $val) {
                $locationId = isset($val->soh_location) ? $val->soh_location : ($val->inbound[0]->inbound_location ? $val->inbound[0]->inbound_location : $val->inbound[0]->whs_name);
                $matgId = $val->matg_id;
                if (!isset($data[$val->material_id.'-'.$locationId])) {
                    $data[$val->material_id.'-'.$locationId]['soh_location'] = $locationId;
                    $data[$val->material_id.'-'.$locationId]['soh_location_name'] = isset($val->soh_location_name) ? $val->soh_location_name : $val->inbound[0]->whs_name;
                    $data[$val->material_id.'-'.$locationId]['UoM'] = isset($val->UoM) ? $val->UoM : $val->uom;
                    $data[$val->material_id.'-'.$locationId]['matg_id'] = $matgId;
                    $data[$val->material_id.'-'.$locationId]['material_id'] = $val->material_id;
                    $data[$val->material_id.'-'.$locationId]['material_name'] = $val->material_name;
                    $data[$val->material_id.'-'.$locationId]['stock_ok'] = isset($val->stock_ok) ? $val->stock_ok : $val->qty_good_in;
                    $data[$val->material_id.'-'.$locationId]['stock_nok'] = isset($val->stock_nok) ? $val->stock_nok : $val->qty_notgood_in;
                    $data[$val->material_id.'-'.$locationId]['created_at'] = date('Y-m-d H:i:s');
                    $data[$val->material_id.'-'.$locationId]['updated_at'] = date('Y-m-d H:i:s');
                    $data[$val->material_id.'-'.$locationId]['source_data'] = $baseApi;
                } else {
                    $data[$val->material_id.'-'.$locationId]['stock_ok'] += isset($val->stock_ok) ? $val->stock_ok : $val->qty_good_in;
                    $data[$val->material_id.'-'.$locationId]['stock_nok'] += isset($val->stock_nok) ? $val->stock_nok : $val->qty_notgood_in;
                }
            }
        }
        // Finalisasi data yang akan dilempar
        $dataFinal = [];        
        foreach ($data as $loc => $val) $dataFinal[] = $val;
        $deletePoslog = PoslogProduct::where(function ($query) use ($matgId, $baseApi) {
            $query->where('matg_id', '=', $matgId);
            if ($baseApi !== 'DASHBOARD_PIKOBAR_API_BASE_URL') {
                $query->where('soh_location', '!=', 'GUDANG LABKES');
            }
        })->delete();
        $insertPoslog = PoslogProduct::insert($dataFinal);
        return true;
    }    

    public function getPoslogItem($matgId, $materialName)
    {
        try{
            $poslogProduct = PoslogProduct::where(function ($query) use ($matgId, $materialName) {
                $query->where('matg_id', '=', $matgId);
                $query->where('stock_ok', '>', 0);
                if ($materialName) $query->where('material_name', 'LIKE', '%' . $materialName . '%');
            })->orderBy('stock_ok','desc')->get();
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
        return $poslogProduct ? $poslogProduct : [];
    }
}
