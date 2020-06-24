<?php

/**
 * Class for storing all method & data regarding item usage information, which 
 * are retrieved from Pelaporan API
 */

namespace App;

use GuzzleHttp;
use JWTAuth;

class Usage
{
    static $client=null;

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
        $res = Usage::getClient()->get($url, [
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
    static function getLogisticStock($param)
    {
        $apiKey = env('DASHBOARD_PIKOBAR_API_KEY');
        $url = env('DASHBOARD_PIKOBAR_API_BASE_URL') . '/api-pt-pos/master/soh';
        $url = $param != '' ? $url . '?where=' . $param : $url;
        $res = static::getClient()->get($url, [
            'headers' => [
                'accept' => 'application/json',
                'api-key' => $apiKey,
            ]
        ]);

        if ($res->getStatusCode() != 200) {
            error_log("Error: dasboard executif pikobar API returning status code ".$res->getStatusCode());
            return [ response()->format(500, 'Internal server error'), null ];
        } else {
            // Extract the data
            return response()->format(200, 'success', json_decode($res->getBody())->data);
        }
    }

}
