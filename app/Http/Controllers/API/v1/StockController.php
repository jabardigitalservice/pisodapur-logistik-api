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
        $product = [];
        $fieldPoslog = '';
        $valuePoslog = '';
        $materialName = false;
        if ($request->filled('poslog_id')) {
            $fieldPoslog = 'material_id';
            $valuePoslog = $request->input('poslog_id');
        } else if ($request->filled('id')) {
            $product = Product::findOrFail($request->input('id'));
            $fieldPoslog = 'matg_id';
            $valuePoslog = $product->material_group;
            if (strpos($product->name, 'VTM') !== false) {
                $materialName = 'VTM';
            }
        }
        $this->syncDatabase($fieldPoslog, $valuePoslog);
        $dataFinal = Usage::getPoslogItem($fieldPoslog, $valuePoslog, $materialName);
        
        return response()->format(200, 'success', $dataFinal);
    }
    
    /**
     * Display a listing of the resource.
     * if did not exists in our database, system will update material list
     *
     * @return \Illuminate\Http\Response
     */
    public function productUnitList($id)
    {
        //Get data
        $data = Usage::getPoslogItem('material_id', $id, false);
        $dataFinal = [];
        foreach ($data as $val) {
            $dataFinal[] = [
                'id' => $val->uom,
                'name' => $val->uom
            ];
        }
        return response()->format(200, 'success', $dataFinal);
    }

    public function syncDatabase($fieldPoslog, $valuePoslog)
    {
        $baseApi = PoslogProduct::API_DASHBOARD;
        if ($this->checkOutdated($fieldPoslog, $valuePoslog, $baseApi)) {
            Usage::syncDashboard(); // Sync from DASHBOARD
        }
        
        $baseApi = PoslogProduct::API_POSLOG;
        if ($this->checkOutdated($fieldPoslog, $valuePoslog, $baseApi)) {
            Usage::syncWmsJabar(); // Sync from WMS JABAR
        }
    }

    public function checkOutdated($field, $value, $baseApi)
    {
        $result = false;
        $updateTime = false;
        if ($field !== 'material_id') {
            $updateTime = $this->getUpdateTime($field, $value, $baseApi);
        }
        $result = $this->isOutdated($updateTime, $baseApi);
        return $result;
    }

    public function getUpdateTime($field, $value, $baseApi)
    {
        try{
            $updateTime = PoslogProduct::where(function ($query) use($field, $value, $baseApi) {
                if (PoslogProduct::isDashboardAPI($baseApi)) {
                    $query->where('soh_location', '=', 'GUDANG LABKES');
                    if ($value) {
                        $query->where($field, '=', $value);
                    }
                }
                $query->where('source_data', '=', $baseApi);
            })->orderBy('updated_at','desc')->value('updated_at');
        } catch (\Exception $exception) {
            $updateTime = null;
        }
        return $updateTime;
    }

    public function isOutdated($updateTime, $baseApi)
    {
        $result = false;
        $time = date('Y-m-d H:i:s'); //Current Time
        $syncTimes = [
            date('Y-m-d H:i:s')
        ];
        if (PoslogProduct::isDashboardAPI($baseApi)) {
            $syncTimes = [
                date('Y-m-d') . ' 12:00:00', //UTC Timezone for 18:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
                date('Y-m-d') . ' 06:00:00', //UTC Timezone for 12:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
                date('Y-m-d') . ' 00:00:00' //UTC Timezone for 06:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
            ];
        } else {
            $updateTime->modify('+5 minutes'); //+ 5 minutes (Sync Time Eestimate)
        }

        foreach ($syncTimes as $syncTime) {
            $extraConditionDashboardAPIOnly = PoslogProduct::isDashboardAPI($baseApi) ? ($time > $syncTime) : true;
            if ($extraConditionDashboardAPIOnly && $updateTime < $syncTime) {
                $result = true;
                break;
            }
        }
        return $result;
    }
}
