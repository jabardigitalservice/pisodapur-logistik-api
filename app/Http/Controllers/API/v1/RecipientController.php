<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Recipient;
use App\Transaction;
use App\City;
use GuzzleHttp;
use DB;

class RecipientController extends Controller
{
    /**
     * Request used rdt stock data from pelaporan dinkes API
     *
     * @return Array [ error, result_array ]
     */
    public function getPelaporanCitySummary()
    {
        // Call external API
        $client = new GuzzleHttp\Client();
        $url = env('PELAPORAN_CITY_SUMMARY_API_URL','');
        return [$url,null];
        $res = $client->get($url);

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
        $query = City::select('kemendagri_kabupaten_kode', 'kemendagri_kabupaten_nama', DB::raw("1000 as total_stock"), DB::raw($queryCase))
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
    public function show(Recipient $recipient)
    {
        return $recipient;
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
}
