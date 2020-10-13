<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Needs;
use App\Agency;
use App\Applicant;
use App\FileUpload;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\LogisticRequestResource;
use App\Letter;
use DB;
use JWTAuth;
use App\Imports\MultipleSheetImport;
use App\Imports\LogisticImport;
use Maatwebsite\Excel\Facades\Excel;
use App\MasterFaskes;
use Illuminate\Support\Facades\Mail;
use App\Mail\LogisticEmailNotification;
use App\LogisticRealizationItems;
use App\Product;
use App\Validation;
use App\Tracking;

class LogisticRequestController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->filled('limit') ? $request->input('limit') : 10;
        $sort = $request->filled('sort') ? ['agency_name ' . $request->input('sort') . ', ', 'updated_at DESC'] : ['updated_at DESC, ', 'agency_name ASC'];
        $data = Agency::getList($request, false);        
        $data = $data->orderByRaw(implode($sort))->paginate($limit);
        return response()->format(200, 'success', $data);
    }

    public function store(Request $request)
    {
        $request = $this->masterFaskesCheck($request);

        $param = [
            'master_faskes_id' => 'required|numeric',
            'agency_type' => 'required|numeric',
            'agency_name' => 'required|string',
            'location_district_code' => 'required|string',
            'location_subdistrict_code' => 'required|string',
            'location_village_code' => 'required|string',
            'applicant_name' => 'required|string',
            'primary_phone_number' => 'required|numeric',
            'logistic_request' => 'required',
            'letter_file' => 'required|mimes:jpeg,jpg,png,pdf|max:10240',
            'application_letter_number' => 'required|string'
        ];
        if (Validation::validate($request, $param)) {
            DB::beginTransaction();
            try {
                $agency = $this->agencyStore($request);
                $request->request->add(['agency_id' => $agency->id]);

                $applicant = $this->applicantStore($request);
                $request->request->add(['applicant_id' => $applicant->id]);

                $need = $this->needStore($request);
                $letter = $this->letterStore($request);
                $email = $this->sendEmailNotification($agency->id, Applicant::STATUS_NOT_VERIFIED);

                $response = [
                    'agency' => $agency,
                    'applicant' => $applicant,
                    'need' => $need,
                    'letter' => $letter
                ];
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                return response()->format(400, $exception->getMessage());
            }
        }
        return response()->format(200, 'success', new LogisticRequestResource($response));
    }

    public function agencyStore($request)
    {
        $request['location_address'] = $request->input('location_address') == 'undefined' ? '' : $request->input('location_address', '');
        $agency = Agency::create($request->all());
        return $agency;
    }

    public function applicantStore($request)
    {
        $fileUpload = null;
        $fileUploadId = null;
        if ($request->hasFile('applicant_file')) {
            $path = Storage::disk('s3')->put('registration/applicant_identity', $request->applicant_file);
            $fileUpload = FileUpload::create(['name' => $path]);
            $fileUploadId = $fileUpload->id;
        }
        $request->request->add(['file' => $fileUploadId, 'verification_status' => Applicant::STATUS_NOT_VERIFIED]);        
        $request['applicants_office'] = $request->input('applicants_office') == 'undefined' ? '' : $request->input('applicants_office', '');
        $request['email'] = $request->input('email') == 'undefined' ? '' : $request->input('email', '');
        $request['secondary_phone_number'] = $request->input('secondary_phone_number') == 'undefined' ? '' : $request->input('secondary_phone_number', '');
        $applicant = Applicant::create($request->all());
        $applicant->file_path = $fileUpload ? Storage::disk('s3')->url($fileUpload->name) : '';
        return $applicant;
    }

    public function needStore($request)
    {
        $response = [];
        foreach (json_decode($request->input('logistic_request'), true) as $key => $value) {
            $need = Needs::create([
                'agency_id' => $request->input('agency_id'),
                'applicant_id' => $request->input('applicant_id'),
                'product_id' => $value['product_id'],
                'brand' => $value['brand'],
                'quantity' => $value['quantity'],
                'unit' => $value['unit'],
                'usage' => $value['usage'],
                'priority' => $value['priority'] ? $value['priority'] : 'Menengah'
            ]);
            $response[] = $need;
        }
        return $response;
    }

    public function letterStore($request)
    {
        $fileUploadId = null;
        if ($request->hasFile('letter_file')) {
            $path = Storage::disk('s3')->put('registration/letter', $request->letter_file);
            $fileUpload = FileUpload::create(['name' => $path]);
            $fileUploadId = $fileUpload->id;
        }
        $request->request->add(['letter' => $fileUploadId]);
        $letter = Letter::create($request->all());
        $letter->file_path = Storage::disk('s3')->url($fileUpload->name);
        return $letter;
    }

    public function show(Request $request, $id)
    {
        $data = Agency::getList($request, true);
        $data = $data->with([
            'letter' => function ($query) {
                return $query->select(['id', 'agency_id', 'letter']);
            }
        ])->where('id', '=', $id)->firstOrFail();
        return response()->format(200, 'success', $data);
    }

    public function verification(Request $request)
    {
        $param = [
            'applicant_id' => 'required|numeric',
            'verification_status' => 'required|string'
        ];
        $param['note'] = $request->verification_status === Applicant::STATUS_REJECTED ? 'required' : '';
        if (Validation::validate($request, $param)) {
            $request['verified_by'] = JWTAuth::user()->id;
            $request['verified_at'] = date('Y-m-d H:i:s');
            $applicant = Applicant::updateApplicant($request);
            $email = $this->sendEmailNotification($applicant->agency_id, $request->verification_status);
        }
        return response()->format(200, 'success', $applicant);
    }

    public function listNeed(Request $request)
    {
        $param = ['agency_id' => 'required'];
        if (Validation::validate($request, $param)) {
            $limit = $request->input('limit', 3);
            $data = Needs::getFields();
            $data = Needs::getListNeed($data, $request)->paginate($limit);
            $logisticItemSummary = Needs::where('needs.agency_id', $request->agency_id)->sum('quantity');
            $data->getCollection()->transform(function ($item, $key) use ($logisticItemSummary) { 
                if (!$item->realization_product_name) {
                    $product = Product::where('id', $item->realization_product_id)->first();
                    $item->realization_product_name = $product ? $product->name : '';
                }
                $item->status = !$item->status ? 'not_approved' : $item->status;
                $item->logistic_item_summary = (int)$logisticItemSummary;
                return $item;
            });
        }
        return response()->format(200, 'success', $data);
    }

    public function import(Request $request)
    {
        $param = ['file' => 'required|mimes:xlsx'];
        if (Validation::validate($request, $param)) {
            DB::beginTransaction();
            try {
                $import = new MultipleSheetImport();
                $ts = Excel::import($import, request()->file('file'));
                LogisticImport::import($import);
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                return response()->format(400, $exception->getMessage());
            }
        }
        return response()->format(200, 'success', '');
    }

    public function requestSummary(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->input('start_date') . ' 00:00:00' : '2020-01-01 00:00:00';
        $endDate = $request->filled('end_date') ? $request->input('end_date') . ' 23:59:59' : date('Y-m-d H:i:s');
        try {
            $lastUpdate = Applicant::Select('applicants.updated_at') 
            ->where('is_deleted', '!=' , 1) 
            ->orderBy('updated_at', 'desc')
            ->first();

            $totalPikobar = Applicant::Select('applicants.id') 
            ->where('source_data', 'pikobar')
            ->where('is_deleted', '!=' , 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

            $totalDinkesprov = Applicant::Select('applicants.id') 
            ->where('source_data', 'dinkes_provinsi')
            ->where('is_deleted', '!=' , 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

            $totalUnverified = Applicant::Select('applicants.id') 
            ->where('approval_status', Applicant::STATUS_NOT_APPROVED) 
            ->where('verification_status', Applicant::STATUS_NOT_VERIFIED) 
            ->where('is_deleted', '!=' , 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

            $totalApproved = Applicant::Select('applicants.id') 
            ->where('approval_status', Applicant::STATUS_APPROVED) 
            ->where('verification_status', Applicant::STATUS_VERIFIED)
            ->whereNull('finalized_by')
            ->where('is_deleted', '!=' , 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

            $totalFinal = Applicant::Select('applicants.id') 
            ->where('approval_status', Applicant::STATUS_APPROVED) 
            ->where('verification_status', Applicant::STATUS_VERIFIED)
            ->whereNotNull('finalized_by')
            ->where('is_deleted', '!=' , 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

            $totalVerified = Applicant::Select('applicants.id') 
            ->where('approval_status', Applicant::STATUS_NOT_APPROVED) 
            ->where('verification_status', Applicant::STATUS_VERIFIED) 
            ->where('is_deleted', '!=' , 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

            $totalVerificationRejected = Applicant::Select('applicants.id') 
            ->where('approval_status', Applicant::STATUS_NOT_APPROVED) 
            ->where('verification_status', Applicant::STATUS_REJECTED)
            ->where('is_deleted', '!=' , 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

            $totalApprovalRejected = Applicant::Select('applicants.id') 
            ->where('approval_status', Applicant::STATUS_REJECTED)
            ->where('verification_status', Applicant::STATUS_VERIFIED)
            ->where('is_deleted', '!=' , 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

            $totalRejected = $totalVerificationRejected + $totalApprovalRejected;
            $total = $totalUnverified + $totalVerified + $totalApproved + $totalFinal + $totalRejected;

            $data = [
                'total_request' => $total,
                'total_approved' => $totalApproved,
                'total_final' => $totalFinal,
                'total_unverified' => $totalUnverified,
                'total_verified' => $totalVerified,
                'total_rejected' => $totalRejected,
                'total_approval_rejected' => $totalApprovalRejected,
                'total_verification_rejected' => $totalVerificationRejected,
                'total_pikobar' => $totalPikobar,
                'total_dinkesprov' => $totalDinkesprov,
                'last_update' => $lastUpdate ? date('Y-m-d H:i:s', strtotime($lastUpdate->updated_at)) : '2020-01-01 00:00:00'
            ];            
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
        return response()->format(200, 'success', $data);
    }

    public function sendEmailNotification($agencyId, $status)
    {
        try {
            $agency = Agency::with(['applicant' => function ($query) {
                return $query->select([
                    'id', 'agency_id', 'applicant_name', 'applicants_office', 'file', 'email', 'primary_phone_number', 'secondary_phone_number', 'verification_status', 'note', 'approval_status', 'approval_note', 'stock_checking_status', 'application_letter_number'
                ])->where('is_deleted', '!=' , 1);
            }])->findOrFail($agencyId);
            Mail::to($agency->applicant['email'])->send(new LogisticEmailNotification($agency, $status));    
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
    }

    public function approval(Request $request)
    {
        $param = [
            'applicant_id' => 'required|numeric',
            'approval_status' => 'required|string'
        ];
        $param['approval_note'] = $request->approval_status === Applicant::STATUS_REJECTED ? 'required' : '';
        if (Validation::validate($request, $param)) {
             // check the list of applications that have not been approved
            $needsSum = Needs::where('applicant_id', $request->applicant_id)->count();
            $realizationSum = LogisticRealizationItems::where('applicant_id', $request->applicant_id)->whereNull('created_by')->count();
            if ($realizationSum != $needsSum && $request->approval_status === Applicant::STATUS_APPROVED) {
                $message = 'Sebelum melakukan persetujuan permohonan, pastikan item barang sudah diupdate terlebih dahulu. Jumlah barang yang belum diupdate sebanyak ' . ($needsSum - $realizationSum) .' item';
                return response()->json([
                    'status' => 422, 
                    'error' => true,
                    'message' => $message,
                    'total_item_need_update' => ($needsSum - $realizationSum)
                ], 422);
            } else {
                $request['approved_by'] = JWTAuth::user()->id;
                $request['approved_at'] = date('Y-m-d H:i:s');
                $applicant = Applicant::updateApplicant($request);
                $email = $this->sendEmailNotification($applicant->agency_id, $request->approval_status);
            }
        }
        return response()->format(200, 'success', $applicant);
    }

    public function final(Request $request)
    {
        $param = [
            'applicant_id' => 'required|numeric',
            'approval_status' => 'required|string'
        ];
        $param['approval_note'] = $request->approval_status === Applicant::STATUS_REJECTED ? 'required' : '';
        if (Validation::validate($request, $param)) {              
            //check the list of applications that have not been approved
            $needsSum = Needs::where('applicant_id', $request->applicant_id)->count();
            $realizationSum = LogisticRealizationItems::where('applicant_id', $request->applicant_id)->whereNotNull('created_by')->count();
            $finalSum = LogisticRealizationItems::where('applicant_id', $request->applicant_id)->whereNotNull('final_by')->count();
            
            if ($finalSum != ($needsSum + $realizationSum) && $request->approval_status === Applicant::STATUS_APPROVED) {
                $message = 'Sebelum menyelesaikan permohonan, pastikan item barang sudah diupdate terlebih dahulu. Jumlah barang yang belum diupdate sebanyak ' . (($needsSum + $realizationSum) - $finalSum) .' item';
                return response()->json([
                    'status' => 422, 
                    'error' => true,
                    'message' => $message,
                    'total_item_need_update' => (($needsSum + $realizationSum) - $finalSum)
                ], 422);
            } else {
                $request['finalized_by'] = JWTAuth::user()->id;
                $request['finalized_at'] = date('Y-m-d H:i:s');
                $applicant = Applicant::updateApplicant($request);                
                $email = $this->sendEmailNotification($applicant->agency_id, $request->approval_status);
            }
        }
        return response()->format(200, 'success', [
            '(needsSum_realization_sum' => ($needsSum + $realizationSum),
            'finalSum' => $finalSum,
            'total_item_need_update' => (($needsSum + $realizationSum) - $finalSum)
        ]);
    }

    public function stockCheking(Request $request)
    {
        $param = [
            'applicant_id' => 'required|numeric',
            'stock_checking_status' => 'required|string'
        ];
        $applicant = (Validation::validate($request, $param)) ? $this->updateApplicant($request) : null;
        return response()->format(200, 'success', $applicant);
    }

    /**
     * Track Function
     * Show application list based on ID, No. HP, or applicant email
     * @param Request $request
     * @return array of Applicant $data
     */
    public function track(Request $request)
    {
        $list = Agency::with([
            'tracking' => function ($query) {
                return $query->select([
                    'id',
                    'agency_id',
                    DB::raw('applicant_name as request'),
                    DB::raw('verification_status'),
                    DB::raw('approval_status'),
                    DB::raw('verification_status as verification'),
                    DB::raw('approval_status as approval'),
                    DB::raw('FALSE as delivering'), // Waiting for Integration data with POSLOG
                    DB::raw('FALSE as delivered'), // Waiting for Integration data with POSLOG
                    DB::raw('concat(approval_status, "-", verification_status) as status'),
                    DB::raw('concat(approval_status, "-", verification_status) as statusDetail'),
                    DB::raw('IFNULL(approval_note, note) as reject_note')
                ])->where('is_deleted', '!=' , 1);
            }
        ])
        ->whereHas('applicant', function ($query) use ($request) { 
            $query->where('id', '=', $request->input('search'));
            $query->orWhere('email', '=', $request->input('search'));
            $query->orWhere('primary_phone_number', '=', $request->input('search'));
            $query->orWhere('secondary_phone_number', '=', $request->input('search'));
        });
        $list = Agency::getDefaultWith($list);
        $list = Agency::whereHasApplicantData($list, $request);
        $list = $list->orderBy('agency.created_at', 'desc')->limit(5)->get();

        $data = [
            'total' => count($list),
            'application' => $list
        ];
        return response()->format(200, 'success', $data);
    }

    /**
     * Track Detail function
     * - return data is pagination so it can receive the parameter limit, page, sorting and filtering / searching
     * @param Request $request
     * @param integer $id
     * @return array of Applicant $data
     */
    public function trackDetail(Request $request, $id)
    {
        $limit = $request->input('limit', 3);
        $select = Tracking::selectFields();
        $logisticRealizationItems = Tracking::getLogisticAdmin($select, $request, $id); //List of item(s) added from admin
        $data = Tracking::getLogisticRequest($select, $request, $id); //List of updated item(s)
        $data = $data->union($logisticRealizationItems)->where('needs.applicant_id', $id)->paginate($limit);
        return response()->format(200, 'success', $data);
    }

    public function masterFaskesCheck($request)
    {
        return $request = (!MasterFaskes::find($request->master_faskes_id)) ? $this->alloableAgencyType($request) : $request;
    }

    public function alloableAgencyType($request)
    {
        if (in_array($request->agency_type, ['4', '5'])) { //allowable agency_type: {agency_type 4 => Masyarakat Umum , agency_type 5 => Instansi Lainnya}
            $param = [
                'agency_type' => 'required|numeric',
                'agency_name' => 'required|string'
            ];
            if (Validation::validate($request, $param)) {
                $masterFaskes = $this->createFaskes($request);
                $request['master_faskes_id'] = $masterFaskes->id;
            }
        } else {
            return response()->json(['status' => 'fail', 'message' => 'agency_type_value_is_not_accepted']);
        }
        return $request;
    }

    public function createFaskes($request)
    {
        try {
            $model = new MasterFaskes();
            $model->fill([
                'id_tipe_faskes' => $request->agency_type,
                'nama_faskes' => $request->agency_name
            ]);
            $model->nomor_izin_sarana = '-';
            $model->nama_atasan = '-';
            $model->point_latitude_longitude = '-';
            $model->verification_status = 'verified';
            $model->is_imported = 0;
            $model->non_medical = 1;
            $model->save();
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
        return $model;
    }
}
