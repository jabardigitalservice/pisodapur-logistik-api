<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Exports\LogisticRequestExport;

class ExportLogisticRequestController extends Controller
{
    public function export(Request $request)
    {
        $export_filename = Carbon::now()->format('Y-m-d_h-i');
        $export_filename = 'export-permohonan-logistik_' . $export_filename . '.xlsx';
        return (new LogisticRequestExport)->download($export_filename);
    }
}
