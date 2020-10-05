<?php

/**
 * Class for storing all method & data regarding item usage information, which 
 * are retrieved from Pelaporan API
 */

namespace App;

use GuzzleHttp;
use JWTAuth;
use DB;
use App\PoslogProduct;
use App\SohLocation;

class Usage
{
    static $client = null;

    static function getClient() 
    {
        if (static::$client == null) {
            static::$client = new GuzzleHttp\Client();
        }

        return static::$client;
    }
    /**
     * Request authorization token from pelaporan API
     *
     * @return Array [ error, result_array ]
     */
    static function getPelaporanAuthToken()
    {
        // login first
        $login_url = env('PELAPORAN_API_BASE_URL') . '/api/login';
        $res = static::getClient()->post($login_url, [
            'json'   => [
                'username' => env('PELAPORAN_AUTH_USER'),
                'password' => env('PELAPORAN_AUTH_PASSWORD'),
            ],
            'verify' => false,
        ]);
        if ($res->getStatusCode() != 200) {
            return [ response()->format(500, 'Internal server error'), null ];
        }
        return json_decode($res->getBody())->data->token;
    }

    /**
     * Request used rdt stock data from pelaporan dinkes API
     *
     * @return Array [ error, result_array ]
     */
    static function getPelaporanCitySummary()
    {
        // retrieving summary by cities endpont
        $token = static::getPelaporanAuthToken();
        $url = env('PELAPORAN_API_BASE_URL') . '/api/rdt/summary-by-cities';
        $res = static::getClient()->get($url, [
            'verify' => false,
            'headers' => [
                'Authorization' => "Bearer $token",
            ],
        ]);

        if ($res->getStatusCode() != 200) {
            error_log("Error: pelaporan API returning status code ".$res->getStatusCode());
            return [ response()->format(500, 'Internal server error'), null ];
        } else {
            // Extract the data
            return [ null,  json_decode($res->getBody())->data ];
        }
    }

    /**
     * Request used RDT result status
     *
     * @return Array [ error, result_array ]
     */
    static function getRdtResultSummary($city_code)
    {
        $token = static::getPelaporanAuthToken();
        $url = env('PELAPORAN_API_BASE_URL') . '/api/rdt/summary-result-by-cities?city_code=' . $city_code;
        $res = static::getClient()->get($url, [
            'verify' => false,
            'headers' => [
                'Authorization' => "Bearer $token",
            ],
        ]);

        if ($res->getStatusCode() != 200) {
            error_log("Error: pelaporan API returning status code ".$res->getStatusCode());
            return [ response()->format(500, 'Internal server error'), null ];
        } else {
            // Extract the data
            return [ null, json_decode($res->getBody())->data ];
        }
    }

    /**
     * Request used RDT result status
     *
     * @return Array [ error, result_array ]
     */
    static function getRdtResultList($city_code)
    {
        $token = static::getPelaporanAuthToken();
        $url = env('PELAPORAN_API_BASE_URL') . '/api/rdt/summary-result-list-by-cities?city_code=' . $city_code;
        $res = static::getClient()->get($url, [
            'verify' => false,
            'headers' => [
                'Authorization' => "Bearer $token",
            ],
        ]);

        if ($res->getStatusCode() != 200) {
            error_log("Error: pelaporan API returning status code ".$res->getStatusCode());
            return [ response()->format(500, 'Internal server error'), null ];
        } else {
            // Extract the data
            return [ null, json_decode($res->getBody())->data ];
        }
    }

    /**
     * Request used rdt stock data (grouped by faskes, filter only from the same
     * kob/kota) from pelaporan dinkes API
     *
     * @return Array [ error, result_array ]
     */
    static function getPelaporanFaskesSummary()
    {
        // retrieving summary by cities endpont
        $token = static::getPelaporanAuthToken();
        $district_code = JWTAuth::user()->code_district_city;
        $url  = env('PELAPORAN_API_BASE_URL') . '/api/rdt/faskes-summary-by-cities';
        $url .= "?district_code=$district_code";

        $res = static::getClient()->get($url, [
            'verify' => false,
            'headers' => [
                'Authorization' => "Bearer $token",
            ],
        ]);

        if ($res->getStatusCode() != 200) {
            error_log("Error: pelaporan API returning status code ".$res->getStatusCode());
            return [ response()->format(500, 'Internal server error'), null ];
        } else {
            // Extract the data
            $raw_list = json_decode($res->getBody())->data;

            // format data
            $faskes_list = [];
            foreach ($raw_list as $row) {
                $faskes_list[] = [
                    "faskes_name" => $row->_id,
                    "total_stock" => null,
                    "total_used" => $row->total,
                ];
            }

            return [ null,  $faskes_list ];
        }
    }

    /**
     * Request logistic stock data obtained from PT POS
     *
     * @return Array [ error, result_array ]
     */
    static function getLogisticStock($param, $api, $baseApi)
    {
        $apiKey = ($baseApi === 'DASHBOARD_PIKOBAR_API_BASE_URL') ? env('DASHBOARD_PIKOBAR_API_KEY') : env('WMS_JABAR_API_KEY');
        $apiLink = ($baseApi === 'DASHBOARD_PIKOBAR_API_BASE_URL') ? env('DASHBOARD_PIKOBAR_API_BASE_URL') : env('WMS_JABAR_BASE_URL');
        $apiFunction = $api ? $api : '/api/soh_fmaterialgroup';
        $url = $apiLink . $apiFunction;
        $res = static::getClient()->get($url, [
            'headers' => [
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $apiKey,
            ],
            'body' => $param
        ]);
        if ($res->getStatusCode() != 200) {
            error_log("Error: WMS Jabar API returning status code ".$res->getStatusCode());
            return [ response()->format(500, 'Internal server error'), null ];
        } else {
            return ($baseApi === 'DASHBOARD_PIKOBAR_API_BASE_URL') ? json_decode($res->getBody())->data : json_decode($res->getBody())->msg;
        }
    }

    /**
     * Request logistic stock data obtained from PT POS
     *
     * @return Array [ error, result_array ]
     */
    static function getLogisticStockByLocation($id)
    {
        $param = '{"soh_location":"' . $id . '"}';
        $apiKey = env('WMS_JABAR_API_KEY');
        $apiLink = env('WMS_JABAR_BASE_URL');
        $apiFunction = '/api/soh_flocation';
        $url = $apiLink . $apiFunction;
        $res = static::getClient()->get($url, [
            'headers' => [
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $apiKey,
            ],
            'body' => $param
        ]);

        if ($res->getStatusCode() != 200) {
            error_log("Error: WMS Jabar API returning status code ".$res->getStatusCode());
            return [ response()->format(500, 'Internal server error'), null ];
        } else {
            return json_decode($res->getBody())->msg;
        }
    }

    static function getPoslogItem($fieldPoslog, $valuePoslog, $materialName)
    {
        $poslogProduct = [];
        try{
            $poslogProduct = PoslogProduct::select(DB::raw('CONCAT("(", material_id, ") ", material_name) as name'), 'material_id', 'material_name', 'soh_location', 'soh_location_name', 'uom', 'matg_id', 'stock_ok', 'stock_nok')
            ->where(function ($query) use ($fieldPoslog, $valuePoslog, $materialName) {
                $query->where('stock_ok', '>', 0);
                if ($valuePoslog) {
                    $query->where($fieldPoslog, '=', $valuePoslog);
                }

                if ($materialName) {
                    $query->where('material_name', 'LIKE', '%' . $materialName . '%');
                }
            })->orderBy('material_name','asc')->orderBy('soh_location','asc')->orderBy('stock_ok','desc')->get();
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
        return $poslogProduct;
    }

    static function syncDashboard()
    {
        $data = [];
        $api = '/master/inbound_detail?search=gsheet';
        $param = '';
        $baseApi = 'DASHBOARD_PIKOBAR_API_BASE_URL';
        $materials = Usage::getLogisticStock($param, $api, $baseApi);
        $data = Usage::setPoslogProduct($materials, $baseApi, $data);
        Usage::updatingPoslogProduct($data, $baseApi);
    }

    static function syncWmsJabar()
    {
        $data = [];
        $sohLocation = SohLocation::all();
        $condition = false;
        $baseApi = 'WMS_JABAR_BASE_URL';
        
        foreach ($sohLocation as $val) {
            $materials = Usage::getLogisticStockByLocation($val['location_id']);            
            $data = Usage::setPoslogProduct($materials, $baseApi, $data);
        }
        Usage::updatingPoslogProduct($data, $baseApi);
    }

    static function setPoslogProduct($materials, $baseApi, $data)
    {
        foreach ($materials as $material) {
            $locationId = isset($material->soh_location) ? $material->soh_location : ($material->inbound[0]->inbound_location ? $material->inbound[0]->inbound_location : $material->inbound[0]->whs_name);
            $stockOk = isset($material->stock_ok) ? $material->stock_ok : $material->qty_good_in;
            $stockNok = isset($material->stock_nok) ? $material->stock_nok : $material->qty_notgood_in;
            if (!isset($data[$material->material_id])) {
                if ($stockOk > 0 && Usage::specialCondition($material, $baseApi)) {
                    $data[$material->material_id . '-' . $locationId] = [
                        'material_id' => $material->material_id,
                        'material_name' => $material->material_name,
                        'soh_location' => $locationId,
                        'soh_location_name' => isset($material->soh_location_name) ? $material->soh_location_name : $material->inbound[0]->whs_name,
                        'UoM' => isset($material->UoM) ? $material->UoM : $material->uom,
                        'matg_id' => $material->matg_id,
                        'stock_ok' => $stockOk,
                        'stock_nok' => $stockNok,
                        'source_data' => $baseApi,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
            } else {
                $data[$material->material_id . '-' . $locationId]['stock_ok'] += $stockOk;
                $data[$material->material_id . '-' . $locationId]['stock_nok'] += $stockNok;
            }
        }
        return $data;
    }

    static function specialCondition($material, $baseApi)
    {
        return ($baseApi === 'DASHBOARD_PIKOBAR_API_BASE_URL') ? $material->inbound[0]->whs_name === 'GUDANG LABKES' : true;
    }

    static function updatingPoslogProduct($data, $baseApi)
    {
        
        $data = array_values($data);
        if ($data) {
            //delete all data from WMS JABAR
            $delete = PoslogProduct::where('source_data', '=', $baseApi)->delete();
            //insert all data from $data
            $insertPoslog = PoslogProduct::insert($data);
        }
    }
}