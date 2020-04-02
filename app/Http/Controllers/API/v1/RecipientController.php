<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Recipient;

class RecipientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $chain = Recipient::query();

        if ($request->query('search','') != '') 
          $chain = $chain->where('name', 'like', '%'.$request->query('search').'%');

        if ($request->query('kabkota_kode','') != '') 
          $chain = $chain->where('district_code', $request->query('kabkota_kode'));

        if ($request->query('sort','') != '') {
          $order = ($request->query('sort') == 'desc')?'desc':'asc';
          $chain = $chain->orderBy('name', $order);
        }
        return $chain->paginate($request->input('limit',20));
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
