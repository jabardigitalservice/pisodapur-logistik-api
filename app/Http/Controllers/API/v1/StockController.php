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

        if (!$dataFinal) {
            $dataFinal[] = [
                'id' => 'PCS',
                'name' => 'PCS'
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
        $updateTime = PoslogProduct::getUpdateTime($field, $value, $baseApi);
        $result = $this->isOutdated($updateTime, $baseApi);
        return $result;
    }

    public function isOutdated($updateTime, $baseApi)
    {
        $time = date('Y-m-d H:i:s');
        return $result = PoslogProduct::isDashboardAPI($baseApi) ? $this->isDashboardAPIOutdate($time, $updateTime) : $this->isWMSJabarAPIOutdate($time, $updateTime);
    }

    public function isDashboardAPIOutdate($time, $updateTime)
    {
        $result = false;
        $syncTimes = [
            date('Y-m-d') . ' 12:00:00', //UTC Timezone for 18:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
            date('Y-m-d') . ' 06:00:00', //UTC Timezone for 12:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
            date('Y-m-d') . ' 00:00:00' //UTC Timezone for 06:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
        ];
        foreach ($syncTimes as $syncTime) {
            $isSyncTime = $time > $syncTime;
            $isDataOutDate = $updateTime < $syncTime;
            if ($isSyncTime && $isDataOutDate) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    public function isWMSJabarAPIOutdate($time, $updateTime)
    {
        $updateTime->modify('+5 minutes'); //+ 5 minutes (Sync Time Eestimate)
        $syncTime = $time;
        return $updateTime < $syncTime ?? false;
    }
}
