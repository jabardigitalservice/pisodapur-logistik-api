<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use JWTAuth;

use App\Http\Controllers\Controller;
use App\Transaction;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $chain = Transaction::with(['user','recipient']);

        if ($request->query('search','') != '') 
          $chain = $chain->where('recipient.name', 'like', 
                                 '%'.$request->query('search').'%');

        if ($request->query('time','') != '') 
          $chain = $chain->whereDate('time', $request->query('time'));

        if ($request->query('kabkota_kode','') != '') 
          $chain = $chain->where('location_district_code', $request->query('kabkota_kode'));

        if ($request->query('kec_kode','') != '') 
          $chain = $chain->where('location_subdistrict_code', $request->query('kec_kode'));

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
        $model = new Transaction();
        $model->fill($request->input());
        $model->id_user = JWTAuth::user()->id;
        if ($model->save()) 
          if ($model->updateRecipient())
            return $model;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Transaction::with(['user','recipient'])->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $model = Transaction::findOrFail($id);
        $model->fill($request->input());
        if ($model->save()) return $model;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = Transaction::findOrFail($id);
        if ($model->delete()) return $model;
    }
}
