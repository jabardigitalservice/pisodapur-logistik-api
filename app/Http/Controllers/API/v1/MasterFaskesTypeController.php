<?php

namespace App\Http\Controllers\API\v1;

use App\MasterFaskesType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class MasterFaskesTypeController extends Controller
{
    public function index(Request $request)
    {

        try {
            $data = MasterFaskesType::where(function ($query) use ($request) {
                if ($request->filled('is_imported')) {
                    $query->where('is_imported', $request->input('is_imported'));
                }
            })->get();
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return response()->format(200, 'success', $data);
    }

    public function masterFaskesTypeRequest(Request $request)
    {
        try {
            $query = MasterFaskesType::selectRaw('master_faskes_types.name, COUNT(agency.agency_type) as total_request')
            ->leftJoin('agency', function($join) {
                $join->on('agency.agency_type', '=', 'master_faskes_types.id');
            })
            ->leftJoin('applicants', function($join) {
                $join->on('applicants.agency_id', '=', 'agency.id');
            })
            ->where('applicants.verification_status', 'verified')
            ->groupBy('master_faskes_types.id');
            if ($request->filled('sort')) {
                $query->orderBy('total_request', $request->input('limit'));
            } 
            $data = $query->get();
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return response()->format(200, 'success', $data);
    } 
}
