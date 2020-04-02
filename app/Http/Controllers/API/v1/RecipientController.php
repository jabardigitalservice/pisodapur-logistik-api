<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Recipient;
use App\City;
use GuzzleHttp;
use DB;

class RecipientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Call external API
        $client = new GuzzleHttp\Client();
        $res = $client->get('https://pikobar-pelaporan-api.digitalservice.id/api/rdt/summary-by-cities');
        if ($res->getStatusCode() != 200) {
            return response()->format(404, 'Object Not Found');
        }

        // Extract the data
        $obj = json_decode($res->getBody());
        $queryCase = 'CASE WHEN kemendagri_kabupaten_kode = 1 THEN 1';
        foreach ($obj->data as $key => $value) {
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

        if ($request->query('sort','') != '') {
            $order = ($request->query('sort') == 'desc')?'desc':'asc';
            $query->orderBy('kemendagri_provinsi_nama', $order);
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
        $summary = [
            "quantity_distributed"  => 1000,
            "quantity_available"    => 0,
            "quantity_used"         => 0,
        ];
        return response()->format(200, 'success', $summary);
    }
}
