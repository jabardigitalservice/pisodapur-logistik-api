<?php 

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\RequestLetter;
use App\Http\Controllers\Controller;
use Validator;
use JWTAuth;
use DB; 
use App\LogisticRealizationItems;

class RequestLetterController extends Controller
{
    public function index(Request $request)
    {        
        $data = [];
        $validator = Validator::make($request->all(), [
            'outgoing_letter_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        } else {
            try { 
                $limit = $request->input('limit', 10);
                $data = RequestLetter::select(
                    'request_letters.id',
                    'request_letters.outgoing_letter_id',
                    'request_letters.applicant_id',
                    'applicants.application_letter_number',
                    'applicants.agency_id',
                    'agency.agency_name',
                    'agency.location_district_code',
                    'districtcities.kemendagri_kabupaten_nama',
                    'applicants.applicant_name',
                    DB::raw('0 as realization_total'),
                    DB::raw('"" as realization_date')
                )
                ->join('applicants', 'applicants.id', '=', 'request_letters.applicant_id')
                ->join('agency', 'agency.id', '=', 'applicants.agency_id')
                ->join('districtcities', 'districtcities.kemendagri_kabupaten_kode', '=', 'agency.location_district_code')
                ->where('request_letters.outgoing_letter_id', $request->outgoing_letter_id)
                ->where(function ($query) use ($request) {
                    if ($request->filled('application_letter_number')) {
                        $query->where('applicants.application_letter_number', 'LIKE', "%{$request->input('application_letter_number')}%");
                    }    
                })
                ->orderBy('request_letters.id')
                ->paginate($limit);
 
                foreach ($data as $key => $val) {
                    $data[$key] = $this->getRealizationData($val);
                }
            } catch (\Exception $exception) {
                return response()->format(400, $exception->getMessage());
            }
        }

        return response()->format(200, 'success', $data);
    }

    /**
     * getRealizationData
     * 
     */
    public function getRealizationData($request_letter)
    {
        $realization_total = LogisticRealizationItems::where('agency_id', $request_letter->agency_id) 
        ->where('applicant_id', $request_letter->applicant_id)
        ->sum('realization_quantity');

        
        $realization = LogisticRealizationItems::where('agency_id', $request_letter->agency_id) 
        ->where('applicant_id', $request_letter->applicant_id)
        ->whereNotNull('realization_date')
        ->first();
        
        $request_letter->realization_total = $realization_total;
        $request_letter->realization_date = $realization['realization_date'];
        
        $data = $request_letter;
        return $data; 
    }
}