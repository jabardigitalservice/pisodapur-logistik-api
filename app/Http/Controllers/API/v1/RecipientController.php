<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use JWTAuth;
use DB;

use App\Http\Controllers\Controller;
use App\Recipient;
use App\Transaction;
use App\City;
use App\Usage;

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
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        list($err, $obj) = Usage::getPelaporanCitySummary();

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
            $query->orderBy('kemendagri_kabupaten_nama', $order);
        }

        $data = $query->paginate($request->input('limit',20));

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
    public function show($cityCode, Request $request)
    {
        $url = env('PELAPORAN_API_BASE_URL') . '/api/rdt/summary-result-list-by-cities?city_code=' . $cityCode;
        list($err, $res) = Usage::getPelaporanData($url);
        if ($err != null) { //error
            return $err;
        }

        // Reformat data
        $res = $res[0];
        $collections = [];
        foreach ($res->total_used_list as $key => $value) {
            $collections[$key]['name'] = $value->_id ;
            $collections[$key]['total_used'] = $value->total_used;

            $totalPositif = 0;
            foreach ($res->total_positif_list as $valPositif) {
                if ($valPositif->_id == $value->_id) {
                    $totalPositif = $valPositif->total_positif;
                }
            }
            $collections[$key]['total_positif'] = $totalPositif;

            $totalNegatif = 0;
            foreach ($res->total_negatif_list as $valNegatif) {
                if ($valNegatif->_id == $value->_id) {
                    $totalNegatif = $valNegatif->total_negatif;
                }
            }
            $collections[$key]['total_negatif'] = $totalNegatif;

            $totalInvalid = 0;
            foreach ($res->total_invalid_list as $valInvalid) {
                if ($valInvalid->_id == $value->_id) {
                    $totalInvalid = $valInvalid->total_invalid;
                }
            }
            $collections[$key]['total_invalid'] = $totalInvalid;
        }

        // Make pagination
        $data = $this->paginateArray($collections, $request);

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
        list($err, $obj) = Usage::getPelaporanCitySummary();
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
     * Request used RDT result status
     *
     * @return \Illuminate\Http\Response
     */
    static function summary_rdt_result(Request $request)
    {
        $city_code = $request->query('city_code');
        $url = env('PELAPORAN_API_BASE_URL') . '/api/rdt/summary-result-by-cities?city_code=' . $city_code;
        list($err, $result) = Usage::getPelaporanData($url);
        if ($err != null) { //error
            return $err;
        }

        return response()->format(200, 'success', $result);
    }
}
