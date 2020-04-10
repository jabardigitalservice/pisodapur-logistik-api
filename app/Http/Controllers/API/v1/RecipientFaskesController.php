<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use JWTAuth;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Exports\RecipientFaskesExport;
use App\Transaction;
use App\Usage;

class RecipientFaskesController extends Controller
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
            if (JWTAuth::user()->roles != 'dinkeskota' ) {
                return response()->format(404, 'You cannot access this page', null);
            }

            return $next($request);
        });
    }

    /**
     * Display a listing of the faskes monitoring data
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        list($err, $faskes_list) = Usage::getPelaporanFaskesSummary();
        if ($err != null) { //error
            return $err;
        }

        if ($request->query('search')) {
            $word = $request->query('search');
            $faskes_list = array_filter($faskes_list, function($val) use ($word) {
              return stripos($val['faskes_name'], $word);
            });
        }

        $order = ($request->query('sort') == 'desc') ?
                  -1 : //desc
                  1 ; //asc
        usort($faskes_list, function($a,$b) use ($order) {
            return $order * strcmp($a['faskes_name'], $b['faskes_name']);
        });

        $data = $this->paginateArray($faskes_list, $request);

        return response()->format(200, 'success', $data);
    }

    /**
     * Retrieve summary for statistical dashboard (ONLY FOR FASKES DATA)
     *
     * @return \Illuminate\Http\Response
     */
    public function summary()
    {
        $district_code = JWTAuth::user()->code_district_city;

        list($err, $faskes_list) = Usage::getPelaporanCitySummary();
        if ($err != null) { //error
            return $err;
        }

        $total_used = 0;
        foreach ($faskes_list as $key => $value) {
            if ($value->_id == $district_code) {
                $total_used = $value->total;
            }
        }

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

    /**
     * Export RecipientFaskes list as excel
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
      $export_filename = Carbon::now()->format('Y-m-d_h-i');
      $export_filename = 'export-faskes-recipients_'.$export_filename.'.xlsx';
      return (new RecipientFaskesExport)->download($export_filename);
    }
}
