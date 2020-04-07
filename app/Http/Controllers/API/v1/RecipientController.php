<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use GuzzleHttp;
use JWTAuth;
use DB;

use App\Http\Controllers\Controller;
use App\Recipient;
use App\Transaction;
use App\City;

class RecipientController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // anonymous middlewares to validate user's role

        $this->middleware(function($request, $next) {
            if (JWTAuth::user()->roles != 'dinkesprov') {
                return response()->format(404, 'You cannot access this page', null);
            }

            return $next($request);
        })->except('index_faskes', 'summary_faskes');

        $this->middleware(function($request, $next) {
            if (JWTAuth::user()->roles != 'dinkeskota' ) {
                return response()->format(404, 'You cannot access this page', null);
            }

            return $next($request);
        })->only('index_faskes', 'summary_faskes');

        $this->client = new GuzzleHttp\Client();
    }

    /**
     * Request authorization token from pelaporan API
     *
     * @return Array [ error, result_array ]
     */
    public function getPelaporanAuthToken()
    {
        // login first
        $login_url = env('PELAPORAN_API_BASE_URL') . '/api/login';
        $res = $this->client->post($login_url, [
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
    public function getPelaporanCitySummary()
    {
        // retrieving summary by cities endpont
        $token = $this->getPelaporanAuthToken();
        $url = env('PELAPORAN_API_BASE_URL') . '/api/rdt/summary-by-cities';
        $res = $this->client->get($url, [
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
     * Request used rdt stock data (grouped by faskes, filter only from the same 
     * kob/kota) from pelaporan dinkes API
     *
     * @return Array [ error, result_array ]
     */
    public function getPelaporanFaskesSummary()
    {
        // retrieving summary by cities endpont
        $token = $this->getPelaporanAuthToken();
        $district_code = JWTAuth::user()->code_district_city;
        $url  = env('PELAPORAN_API_BASE_URL') . '/api/rdt/faskes-summary-by-cities';
        $url .= "?district_code=$district_code";

        $res = $this->client->get($url, [
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        list($err, $obj) = $this->getPelaporanCitySummary();

        if ($err != null) { //error
            return $err;
        }

        // Extract the data
        $queryCase = 'CASE WHEN kemendagri_kabupaten_kode = 1 THEN 1';
        foreach ($obj as $key => $value) {
            if ($value->_id != '') {
                $queryCase .= " WHEN kemendagri_kabupaten_kode = $value->_id THEN $value->total ";
            }
        }
        $queryCase .= 'ELSE 0 END as total_used';

        // Query summary
        $query = City::select(
                    'kemendagri_kabupaten_kode',
                    'kemendagri_kabupaten_nama',
                    DB::raw('(select ifnull(abs(sum(quantity)), 0) from transactions t where t.location_district_code = kemendagri_kabupaten_kode and quantity < 0 ) as total_stock'),
                    DB::raw($queryCase))
                    ->where('kemendagri_provinsi_kode', '32');

        if ($request->query('search')) {
            $query->where('kemendagri_kabupaten_nama', 'like', '%'.$request->query('search').'%');
        }

        if ($request->query('city_code')) {
            $query->where('kemendagri_kabupaten_kode', '=', $request->query('city_code'));
        }

        if ($request->query('sort')) {
            $order = ($request->query('sort') == 'desc') ? 'desc':'asc';
            $query->orderBy('kemendagri_kabupaten_kode', $order);
        }

        $data = $query->paginate($request->input('limit',20));

        return response()->format(200, 'success', $data);
    }

    /**
     * Display a listing of the faskes monitoring data
     *
     * @return \Illuminate\Http\Response
     */
    public function index_faskes(Request $request)
    {
        list($err, $faskes_list) = $this->getPelaporanFaskesSummary();
        if ($err != null) { //error
            return $err;
        }

        // paginator untuk data berupa array
        // ref : https://arjunphp.com/laravel-5-pagination-array/
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($faskes_list); // Create a new Laravel collection from the array data
        $perPage = $request->input('limit',20); // Define how many items we want to be visible in each page
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all(); // Slice the collection to get the items to display in current page
        $data = new \Illuminate\Pagination\LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage); // Create our paginator and pass it to the view
        $data->setPath($request->url()); // set url path for generted links

        return response()->format(200, 'success', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return Recipient::create($request->input());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Recipient  $recipient
     * @return \Illuminate\Http\Response
     */
    public function show($cityCode)
    {
        $data = [];
        return response()->format(200, 'success', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Recipient  $recipient
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Recipient $recipient)
    {
        $recipient->fill($request->input());
        if ($request->save()) return $return;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Recipient  $recipient
     * @return \Illuminate\Http\Response
     */
    public function destroy(Recipient $recipient)
    {
        if ($recipient->delete()) return $model;
    }

    /**
     * Retrieve summary for statistical dashboard
     *
     * @return \Illuminate\Http\Response
     */
    public function summary()
    {
        list($err, $obj) = $this->getPelaporanCitySummary();
        if ($err != null) { //error
            return $err;
        }
        $total_used = 0;
        foreach ($obj as $key => $value) {
            if ($value->_id != '') {
                $total_used += $value->total;
            }
        }

        $total_distributed = abs( Transaction::selectRaw('SUM(quantity) as t')->where('quantity','<',0)->first()['t'] );

        $summary = [
            "quantity_distributed"  => $total_distributed,
            "quantity_available"    => $total_distributed-$total_used,
            "quantity_used"         => $total_used,
        ];
        return response()->format(200, 'success', $summary);
    }
    /**
     * Retrieve summary for statistical dashboard (ONLY FOR FASKES DATA)
     *
     * @return \Illuminate\Http\Response
     */
    public function summary_faskes()
    {
        list($err, $faskes_list) = $this->getPelaporanFaskesSummary();
        if ($err != null) { //error
            return $err;
        }

        $total_used = 0;
        foreach ($faskes_list as $key => $value) {
            if ($value->_id != '') {
                $total_used += $value->total;
            }
        }

        $district_code = JWTAuth::user()->code_district_city;
        $total_distributed = abs( Transaction::selectRaw('SUM(quantity) as t')
            ->where('quantity','<',0)
            ->where('location_district_code', $district_code)
            ->first()['t'] );

        $summary = [
            "quantity_distributed"  => $total_distributed,
            "quantity_used"         => $total_used,
            "quantity_available"    => $total_distributed-$total_used,
        ];
        return response()->format(200, 'success', $summary);
    }
}
