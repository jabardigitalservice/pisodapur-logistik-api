<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Applicant;
use App\RequestLetter;
use DB;

class IncomingLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = []; 
        $limit = $request->input('limit', 10);
        $sort = $request->filled('sort') ? ['applicants.created_at ' . $request->input('sort') ] : ['applicants.created_at ASC'];

        try {
            $data = Applicant::select(
                    'applicants.id',
                    'applicants.application_letter_number',
                    'applicants.agency_id',  
                    'agency.agency_name',  
                    'agency.location_district_code as district_code',  
                    'districtcities.kemendagri_kabupaten_nama as district_name',  
                    'applicants.applicant_name',
                    'applicants.created_at as letter_date',
                    DB::raw('"Belum Ada Surat Keluar" as status')
                )
                ->where(function ($query) use ($request) {
                    if ($request->filled('letter_date')) {
                        $query->whereDate('applicants.created_at', '=', $request->input('letter_date'));
                    }
                    if ($request->filled('district_code')) {
                        $query->where('agency.location_district_code', '=', $request->input('district_code'));
                    }
                    if ($request->filled('agency_id')) {
                        $query->where('applicants.agency_id', '=', $request->input('agency_id'));
                    }
                })
                ->join('agency', 'agency.id', '=', 'applicants.agency_id')
                ->join('districtcities', 'districtcities.kemendagri_kabupaten_kode', '=', 'agency.location_district_code')
                ->where('applicants.is_deleted', '!=', 1)
                ->orderByRaw(implode($sort))->paginate($limit);

            $data->getCollection()->transform(function ($applicant, $key) {
                $find = RequestLetter::where('applicant_id', $applicant->id)->first();
                $applicant->status = $find ? 'Ada Surat Keluar' : $applicant->status;
                return $applicant;
            });
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return response()->format(200, 'success', $data);
    }
}
