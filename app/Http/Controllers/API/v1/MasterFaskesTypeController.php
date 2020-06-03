<?php

namespace App\Http\Controllers\API\v1;

use App\MasterFaskesType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
            $startDate = $request->filled('start_date') ? $request->input('start_date') : '2020-01-01';
            $endDate = $request->filled('end_date') ? $request->input('end_date') : date('Y-m-d');

            $query = MasterFaskesType::withCount([
                'agency as total_request' => function ($query) use ($startDate, $endDate) {
                    $query->join('applicants', 'applicants.agency_id', 'agency.id')
                        ->where('applicants.verification_status', 'verified');
                    $query->whereBetween('applicants.updated_at', [$startDate, $endDate]);
                    return $query;
                }
                ]);
            if ($request->filled('sort')) {
                $query->orderBy('total_request', $request->input('sort'));
            }
            $data = $query->get();
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return response()->format(200, 'success', $data);
    } 
}
