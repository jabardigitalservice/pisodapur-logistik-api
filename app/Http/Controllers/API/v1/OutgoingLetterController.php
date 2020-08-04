<?php

namespace App\Http\Controllers\API\v1;

use App\OutgoingLetter;
use App\RequestLetter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use JWTAuth;
use DB;
use App\Needs;

class OutgoingLetterController extends Controller
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
        $sort = $request->filled('sort') ? ['letter_date ' . $request->input('sort') ] : ['letter_date ASC'];

        try {
            $data = OutgoingLetter::select(
                'id', 
                'letter_number', 
                'letter_date', 
                DB::raw('0 as request_letter_total'), 
                'status', 
                'filename', 
                'created_at', 
                'updated_at'
            ) 
            ->where('user_id',  JWTAuth::user()->id)
            ->where(function ($query) use ($request) {
                if ($request->filled('letter_number')) {
                    $query->where('letter_number', 'LIKE', "%{$request->input('letter_number')}%");
                }

                if ($request->filled('letter_date')) {
                    $query->where('letter_date', $request->input('letter_date'));
                }
            })         
            ->orderByRaw(implode($sort))
            ->paginate($limit);

            foreach ($data as $key => $value) {
                $data[$key]['request_letter_total'] = $this->getRequestLetterTotal($value['id']);
            }
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

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
        $response = [];
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'letter_number' => 'required',
                    'letter_date' => 'required',
                    'letter_request' => 'required',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->format(422, $validator->errors());
        } else {
            DB::beginTransaction();
            try {
                $request->request->add(['user_id' => JWTAuth::user()->id]);
                $request->request->add(['status' =>  OutgoingLetter::NOT_APPROVED]);
                $outgoing_letter = $this->outgoingLetterStore($request);
                
                $request->request->add(['outgoing_letter_id' => $outgoing_letter->id]);
                $request_letter = $this->requestLetterStore($request);

                $response = array(
                    'outgoing_letter' => $outgoing_letter,
                    'request_letter' => $request_letter,
                );

                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                return response()->format(400, $exception->getMessage());
            }
        }
        
        return response()->format(200, 'success', $response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\OutgoingLetter  $outgoingLetter
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $data = [];

        $limit = $request->input('limit', 10);
        try {
            $outgoingLetter = OutgoingLetter::find($id);
            $requestLetter = RequestLetter::select(
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
            ->where(function ($query) use ($request) {
                if ($request->filled('application_letter_number')) {
                    $query->where('applicants.application_letter_number', 'LIKE', "%{$request->input('application_letter_number')}%");
                }

            })
            ->where('request_letters.outgoing_letter_id', $id)
            ->orderBy('request_letters.id')
            ->paginate($limit);

            $requestLetterProcess = [];
            foreach ($requestLetter as $key => $val) {
                $requestLetterProcess[] = $this->getRealizationData($val);
            }

            $requestLetter = $requestLetterProcess;

            $data = [
                'outgoing_letter' => $outgoingLetter,
                'request_letter' => $requestLetter
            ];
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return response()->format(200, 'success', $data);
    }

    /**
     * Store Outgoing Letter
     *
     * @param  \App\OutgoingLetter  $outgoingLetter
     * @return \Illuminate\Http\Response
     */
    public function outgoingLetterStore($request)
    {
        $outgoing_letter = OutgoingLetter::create($request->all());
        return $outgoing_letter;
    }

    /**
     * Store Request Letter
     * 
     */
    public function requestLetterStore($request)
    {
        $response = [];
        foreach (json_decode($request->input('letter_request'), true) as $key => $value) {
            $request_letter = RequestLetter::create(
                [
                    'outgoing_letter_id' => $request->input('outgoing_letter_id'), 
                    'applicant_id' => $value['applicant_id']
                ]
            );
            $response[] = $request_letter;
        }

        return $response;
    }

    /**
     * getRealizationData
     * 
     */
    public function getRealizationData($request_letter)
    {
        $realization_total = Needs::join('logistic_realization_items', 'logistic_realization_items.need_id', '=', 'needs.id', 'left')
        ->where('needs.agency_id', $request_letter->agency_id)
        ->where('needs.applicant_id', $request_letter->applicant_id)
        ->sum('logistic_realization_items.realization_quantity');

        
        $realization = Needs::select('logistic_realization_items.realization_date')
        ->join('logistic_realization_items', 'logistic_realization_items.need_id', '=', 'needs.id', 'left')
        ->where('needs.agency_id', $request_letter->agency_id)
        ->where('needs.applicant_id', $request_letter->applicant_id)
        ->whereNotNull('logistic_realization_items.realization_date')
        ->first();
        
        $request_letter->realization_date = $realization['realization_date'];
        
        $data = $request_letter;
        return $data;
    }

    public function getRequestLetterTotal($id)
    {
        $data = RequestLetter::where('outgoing_letter_id', $id)->get();
        return count($data);
    }
}
