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
use App\Applicant;
use App\Usage;
use App\LogisticRealizationItems;
use App\FileUpload;
use Illuminate\Support\Facades\Storage;

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
        $sortType = $request->input('sort', 'DESC');

        try {
            $data = OutgoingLetter::where('user_id',  JWTAuth::user()->id)
            ->where(function ($query) use ($request) {
                if ($request->filled('letter_number')) {
                    $query->where('letter_number', 'LIKE', "%{$request->input('letter_number')}%");
                }

                if ($request->filled('letter_date')) {
                    $query->where('letter_date', $request->input('letter_date'));
                }
            })         
            ->orderBy('letter_date', $sortType)
            ->orderBy('created_at', $sortType)
            ->paginate($limit);
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
        
        // Validasi Nomor Surat Keluar harus unik
        $validLetterNumber = OutgoingLetter::where('letter_number', $request->input('letter_number'))->exists();

        if ($validator->fails()) {
            return response()->format(422, $validator->errors());
        } if ($validLetterNumber) {
            return response()->format(422, 'Nomor Surat Keluar sudah digunakan.');
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
            $data = [
                'outgoing_letter' => $outgoingLetter 
            ];
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return response()->format(200, 'success', $data);
    }

    /**
     * Print Function
     * Return spesific data for print outgoing letter format
     *
     * @param  integer $id
     * @return \Illuminate\Http\Response
     */
    public function print($id)
    {
        $data = [];
        try {
            $outgoingLetter = OutgoingLetter::select(
                'id',
                'letter_number',
                'letter_date'
            )->find($id);

            $requestLetter = RequestLetter::select(
                'request_letters.id',
                'request_letters.outgoing_letter_id',
                'request_letters.applicant_id',
                'applicants.application_letter_number',
                'applicants.agency_id',
                'agency.agency_name',
                'agency.location_district_code',
                'districtcities.kemendagri_kabupaten_nama',
                'applicants.applicant_name'
            )
            ->join('applicants', 'applicants.id', '=', 'request_letters.applicant_id')
            ->join('agency', 'agency.id', '=', 'applicants.agency_id')
            ->join('districtcities', 'districtcities.kemendagri_kabupaten_kode', '=', 'agency.location_district_code')
            ->where('request_letters.outgoing_letter_id', $id)
            ->orderBy('request_letters.id')
            ->get();

            $materials = $this->getAllMaterials($requestLetter);

            //Return Image to base64 format
            $pathPemprov = env('AWS_CLOUDFRONT_URL') . 'logo/pemprov_jabar.png';
            $pathDivLog = env('AWS_CLOUDFRONT_URL') . 'logo/divisi_managemen_logistik.png';
            $dataPemprov = file_get_contents($pathPemprov);
            $dataDivlog = file_get_contents($pathDivLog);
            $pemprovLogo = 'data:image/png;base64,' . base64_encode($dataPemprov);
            $divlogLogo = 'data:image/png;base64,' . base64_encode($dataDivlog);

            $data = [
                'image' => [
                    'pemprov' => $pemprovLogo,
                    'divlog' => $divlogLogo,
                ],
                'outgoing_letter' => $outgoingLetter,
                'request_letter' => $requestLetter,
                'material' => $materials,
            ];
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return response()->format(200, 'success', $data);
    }

    public function upload(Request $request)
    {         
        $data = [];
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'id' => 'numeric|required',
                    'outgoing_letter_file' => 'required|mimes:jpeg,jpg,png,pdf|max:10240'
                ]
            )
        );

        if ($validator->fails()) {
            return response()->format(422, $validator->errors());
        } else {
            try{
                //Put File to folder 'outgoing_letter'
                $path = Storage::disk('s3')->put('outgoing_letter', $request->outgoing_letter_file);
                //Create fileupload data
                $fileUpload = FileUpload::create(['name' => $path]);
                //Get ID
                $fileUploadId = $fileUpload->id;
                //Get File Path
                $filePath = Storage::disk('s3')->url($fileUpload->name);
                //Update file to Outgoing Letter by ID  
                $update = OutgoingLetter::where('id', $request->id)->update([
                    'file' => $fileUploadId,
                    'status' => OutgoingLetter::APPROVED //Asumsi bahwa file yang diupload sudah bertandatangan basah
                ]);
            } catch (\Exception $exception) {
                //Return Error Exception
                return response()->format(400, $exception->getMessage());
            }
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
            $request_letter = RequestLetter::firstOrCreate(
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
     * Get All Materials from Selected Application Letters function
     *
     * @param App\RequestLetter $requestLetter
     * @return array $data
     */
    public function getAllMaterials($requestLetter)
    {
        $requestLetterList = [];
        foreach ($requestLetter as $key => $value) {
            $requestLetterList[] = $value['applicant_id'];
        }

        $data = LogisticRealizationItems::select(
            'product_id',
            'product_name',
            'realization_unit',
            'material_group',
            DB::raw('sum(realization_quantity) as realization_quantity'),
            DB::raw('"" as location')
        )
        ->whereIn('applicant_id', $requestLetterList)
        ->whereIn('status', ['approved', 'replaced'])
        ->groupBy(
            'product_id',
            'product_name',
            'realization_unit',
            'material_group',
            'realization_quantity'
        )->get();
        
        foreach ($data as $key => $val) {        
            $param = '{"material_id":"' . $val['product_id'] . '"}';
            $api = '/api/soh_fmaterial';
            $location = "";
            $baseApi = '';
            $retApi = Usage::getLogisticStock($param, $api, $baseApi);
            if (is_array($retApi) || is_object($retApi)) {  
                foreach ($retApi as $val) {
                    $location = $val->soh_location_name;
                    break;
                }
            }
            $data[$key]['location'] = $location;
        }
        return $data;
    }
}
