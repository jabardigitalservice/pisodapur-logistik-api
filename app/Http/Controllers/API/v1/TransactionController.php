<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use Carbon\Carbon;
use JWTAuth;

use App\Http\Controllers\Controller;
use App\Transaction;
use App\Exports\TransactionExport;

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
        $chain = $chain->where('quantity','<',0); // only display outgoing transaction

        if ($request->query('search','') != '')  {
          $chain = $chain->where('name', 'like', 
                                 '%'.$request->query('search').'%');
        }

        if ($request->query('time','') != '') {
          $chain = $chain->whereDate('time', $request->query('time'));
        }

        if ($request->query('kabkota_kode','') != '') {
          $chain = $chain->where('location_district_code', $request->query('kabkota_kode'));
        }

        if ($request->query('kec_kode','') != '') {
          $chain = $chain->where('location_subdistrict_code', $request->query('kec_kode'));
        }

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
        if ($model->save()) {
          //if ($model->updateRecipient())
            return $model;
        }
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

    /**
     * Retrieve summary for statistical dashboard
     *
     * @return \Illuminate\Http\Response
     */
    public function summary()
    {
        $total_in = (int)Transaction::selectRaw('SUM(quantity) as total')->where('quantity','>',0)->first()['total'];
        $total_out = abs(Transaction::selectRaw('SUM(quantity) as total')->where('quantity','<',0)->first()['total']);
        $summary = [
            "quantity_original"     => $total_in,
            "quantity_distributed"  => $total_out ,
            "quantity_available"    => $total_in - $total_out,
        ];
        return response()->format(200, 'success', $summary);
    }

    /**
     * Export Transaction list as excel
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
      $export_filename = Carbon::now()->format('Y-m-d_h-i');
      $export_filename = 'export-distribution_'.$export_filename.'.xlsx';
      return (new TransactionExport)->download($export_filename);
    }
}
