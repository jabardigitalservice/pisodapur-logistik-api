<?php

namespace App\Http\Controllers\API\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Usage;
use App\Product;
use App\PoslogProduct;
use App\SohLocation;

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
        $dataFinal = [];
        $product = [];
        $fieldPoslog = '';
        $valuePoslog = '';
        $materialName = false;
        try {
            if ($request->filled('poslog_id')) {
                $product = PoslogProduct::where('material_id', '=', $request->input('poslog_id'))->firstOrFail();
                $fieldPoslog = 'material_id';
                $valuePoslog = $product->material_id;
            } else if ($request->filled('id')) {
                $product = Product::findOrFail($request->input('id'));
                $fieldPoslog = 'matg_id';
                $valuePoslog = $product->material_group;
                if (strpos($product->name, 'VTM') !== false) {
                    $materialName = 'VTM';
                }
            }
            if ($this->dashboardPoslogItemOutdated($fieldPoslog, $valuePoslog, $product)) {
                Usage::syncDashboard(); // Sync from DASHBOARD
            }
            if ($this->WmsJabarPoslogItemOutdated($fieldPoslog, $valuePoslog, $product)) {                
                Usage::syncWmsJabar(); // Sync from WMS JABAR
            }
            //Get data
            $dataFinal = Usage::getPoslogItem($fieldPoslog, $valuePoslog, $materialName);
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
        
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
    
    public function dashboardPoslogItemOutdated($field, $value, $product)
    {
        $baseApi = 'DASHBOARD_PIKOBAR_API_BASE_URL';
        $now = date('Y-m-d H:i:s');
        $firstSyncTime = date('Y-m-d') . ' 00:00:00'; //UTC Timezone for 06:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
        $secondSyncTime = date('Y-m-d') . ' 06:00:00'; //UTC Timezone for 12:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
        $thirdSyncTime = date('Y-m-d') . ' 12:00:00'; //UTC Timezone for 18:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
        $result = false;        
        $poslogProduct = $product;
        try {
            if ($field !== 'material_id') {
                $poslogProduct = PoslogProduct::where(function ($query) use($field, $value, $baseApi) {
                    if ($value) {  
                        $query->where($field, '=', $value);
                    }
                    $query->where('soh_location', '=', 'GUDANG LABKES');
                    $query->where('source_data', '=', $baseApi);
                })->orderBy('updated_at','desc')->firstOrFail();
            }
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
    
    public function WmsJabarPoslogItemOutdated($field, $value, $product)
    {
        $baseApi = 'WMS_JABAR_BASE_URL';
        $now = date('Y-m-d H:i:s', strtotime('+ 1 Hour')); //UTC Timezone for 06:00 Asia/Jakarta + 1 Hour (Sync Time Eestimate)
        $result = false;
        $poslogProduct = $product;
        try {
            if ($field !== 'material_id') {
                $poslogProduct = PoslogProduct::where('source_data', '=', $baseApi)->orderBy('updated_at','desc')->firstOrFail();
            }
            $SyncTime = date('Y-m-d H:i:s', strtotime($poslogProduct->updated_at) + 60*60); //UTC Timezone + 1 Hour (Sync Time Eestimate)
            if ($SyncTime < date('Y-m-d H:i:s')) {
                $result = true;
            }
        } catch (\Exception $exception) {
            $result = true;
        }

        return $result;
    }
}
