<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;
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

class LogisticRequestController extends Controller
{
    public function index(Request $request)
    {
        if (JWTAuth::user()->roles != 'dinkesprov') {
            return response()->format(404, 'You cannot access this page', null);
        }
        $limit = $request->filled('limit') ? $request->input('limit') : 10;
        $data = Agency::getList($request);
        $data = $data->paginate($limit);
        return response()->format(200, 'success', $data);
    }

    public function store(Request $request)
    {
        if (!MasterFaskes::find($request->master_faskes_id)) {
            if (in_array($request->agency_type, ['4', '5'])) { //allowable agency_type: {agency_type 4 => Masyarakat Umum , agency_type 5 => Instansi Lainnya}
                $validator = Validator::make(
                    $request->all(), array_merge([
                        'agency_type' => 'required|numeric',
                        'agency_name' => 'required|string'
                    ])
                );
                if ($validator->fails()) {
                    return response()->format(422, $validator->errors());
                } else {
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
                    if ($model->save()) {
                        $request = $request->merge([$request, 'master_faskes_id' => $model->id]);
                    }
                }
            } else {
                return response()->json(['status' => 'fail', 'message' => 'agency_type_value_is_not_accepted']);
            }
        }
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
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
                ]
            )
        );

        if ($validator->fails()) {
            return response()->format(422, $validator->errors());
        } else {
            DB::beginTransaction();
            try {
                $agency = $this->agencyStore($request);
                $request->request->add(['agency_id' => $agency->id]);

                $applicant = $this->applicantStore($request);
                $request->request->add(['applicant_id' => $applicant->id]);

                $need = $this->needStore($request);
                $letter = $this->letterStore($request);
                $email = $this->sendEmailNotification($agency->id, Applicant::STATUS_NOT_VERIFIED);

                $response = array(
                    'agency' => $agency,
                    'applicant' => $applicant,
                    'need' => $need,
                    'letter' => $letter
                );
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
            $need = Needs::create(
                [
                    'agency_id' => $request->input('agency_id'),
                    'applicant_id' => $request->input('applicant_id'),
                    'product_id' => $value['product_id'],
                    'brand' => $value['brand'],
                    'quantity' => $value['quantity'],
                    'unit' => $value['unit'],
                    'usage' => $value['usage'],
                    'priority' => $value['priority'] ? $value['priority'] : 'Menengah'
                ]
            );
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

    public function show($id)
    {
        $data = Agency::with([
            'masterFaskesType' => function ($query) {
                return $query->select(['id', 'name']);
            },
            'applicant' => function ($query) {
                return $query->select([
                    'id', 'agency_id', 'applicant_name', 'applicants_office', 'file', 'email', 'primary_phone_number', 'secondary_phone_number', 'verification_status', 'note', 'approval_status', 'approval_note', 'stock_checking_status', 'application_letter_number', 'verified_by', 'verified_at', 'approved_by', 'approved_at', 'finalized_by', 'finalized_at'
                ])->with([
                    'verifiedBy' => function ($query) {
                        return $query->select(['id', 'name', 'agency_name', 'handphone']);
                    },                    
                    'approvedBy' => function ($query) {
                        return $query->select(['id', 'name', 'agency_name', 'handphone']);
                    },                    
                    'finalizedBy' => function ($query) {
                        return $query->select(['id', 'name', 'agency_name', 'handphone']);
                    }
                ])->where('is_deleted', '!=' , 1);
            },
            'letter' => function ($query) {
                return $query->select(['id', 'agency_id', 'letter']);
            },
            'city' => function ($query) {
                return $query->select(['id', 'kemendagri_provinsi_nama', 'kemendagri_kabupaten_kode', 'kemendagri_kabupaten_nama']);
            },
            'subDistrict' => function ($query) {
                return $query->select(['id', 'kemendagri_kecamatan_kode', 'kemendagri_kecamatan_nama']);
            },
            'village' => function ($query) {
                return $query->select(['id', 'kemendagri_desa_kode', 'kemendagri_desa_nama']);
            }
        ])->findOrFail($id);

        return response()->format(200, 'success', $data);
    }

    public function verification(Request $request)
    {
        $rule = [
            'applicant_id' => 'required|numeric',
            'verification_status' => 'required|string'
        ];
        $rule['note'] = $request->verification_status === Applicant::STATUS_REJECTED ? 'required' : '';
        $validator = Validator::make(
            $request->all(),
            array_merge($rule)
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $applicant = Applicant::where('id', $request->applicant_id)
                ->where('is_deleted', '!=' , 1)
                ->firstOrFail();

            $applicant->verification_status = $request->verification_status;
            $applicant->note = $request->note;
            $applicant->verified_by = JWTAuth::user()->id;
            $applicant->verified_at = date('Y-m-d H:i:s');
            $applicant->save();
            $email = $this->sendEmailNotification($applicant->agency_id, $request->verification_status);
        }

        return response()->format(200, 'success', $applicant);
    }

    public function listNeed(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                ['agency_id' => 'required']
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $limit = $request->input('limit', 10);
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
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'file' => 'required|mimes:xlsx',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->format(422, $validator->errors());
        } else {
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
        try {
            $rule = [
                'applicant_id' => 'required|numeric',
                'approval_status' => 'required|string'
            ];
            $rule['approval_note'] = $request->approval_status === Applicant::STATUS_REJECTED ? 'required' : '';
            $validator = Validator::make(
                $request->all(),
                array_merge($rule)
            );
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } else {
                //check the list of applications that have not been approved
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
                    $applicant = Applicant::where('id', $request->applicant_id)->where('is_deleted', '!=' , 1)->firstOrFail();
                    $applicant->fill($request->input());
                    $applicant->approved_by = JWTAuth::user()->id;
                    $applicant->approved_at = date('Y-m-d H:i:s');
                    $applicant->save();
                    $email = $this->sendEmailNotification($applicant->agency_id, $request->approval_status);
                }
            }
            return response()->format(200, 'success', $applicant);
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
    }

    public function final(Request $request)
    {
        try {
            $rule = [
                'applicant_id' => 'required|numeric',
                'approval_status' => 'required|string'
            ];
            $rule['approval_note'] = $request->approval_status === Applicant::STATUS_REJECTED ? 'required' : '';
            $validator = Validator::make(
                $request->all(),
                array_merge($rule)
            );
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } else {
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
                    $applicant = Applicant::where('id', $request->applicant_id)->where('is_deleted', '!=' , 1)->firstOrFail();
                    $applicant->fill($request->input());
                    $applicant->finalized_by = JWTAuth::user()->id;
                    $applicant->finalized_at = date('Y-m-d H:i:s');
                    $applicant->save();
                    $email = $this->sendEmailNotification($applicant->agency_id, $request->approval_status);
                }
            }
            return response()->format(200, 'success', [
                '(needsSum_realization_sum' => ($needsSum + $realizationSum),
                'finalSum' => $finalSum,
                'total_item_need_update' => (($needsSum + $realizationSum) - $finalSum)
            ]);
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
    }

    public function stockCheking(Request $request)
    {
        try {
            $rule = [
                'applicant_id' => 'required|numeric',
                'stock_checking_status' => 'required|string'
            ];
            $validator = Validator::make(
                $request->all(),
                array_merge($rule)
            );
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } else {
                $applicant = Applicant::where('id', $request->applicant_id)->where('is_deleted', '!=' , 1)->firstOrFail();
                $applicant->fill($request->input());
                $applicant->save();
            }
            return response()->format(200, 'success', $applicant);
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
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
            'masterFaskesType' => function ($query) {
                return $query->select(['id', 'name']);
            },
            'applicant' => function ($query) {
                return $query->select([
                    'id', 'agency_id', 'applicant_name', 'applicants_office', 'file', 'email', 'primary_phone_number', 'secondary_phone_number', 'verification_status', 'note', 'approval_status', 'approval_note', 'stock_checking_status', 'application_letter_number'
                ])->where('is_deleted', '!=' , 1);
            },
            'city' => function ($query) {
                return $query->select(['kemendagri_kabupaten_kode', 'kemendagri_kabupaten_nama']);
            },
            'subDistrict' => function ($query) {
                return $query->select(['kemendagri_kecamatan_kode', 'kemendagri_kecamatan_nama']);
            },
            'village' => function ($query) {
                return $query->select(['kemendagri_desa_kode', 'kemendagri_desa_nama']);
            },
            'tracking' => function ($query) {                
                return $query->select([
                    'id',
                    'agency_id',
                    DB::raw('applicant_name as request'),
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
            $query->where('is_deleted', '!=', 1);
        })
        ->whereHas('applicant', function ($query) use ($request) { 
            $query->where('id', '=', $request->input('search'));
            $query->orWhere('email', '=', $request->input('search'));
            $query->orWhere('primary_phone_number', '=', $request->input('search'));
            $query->orWhere('secondary_phone_number', '=', $request->input('search'));
        })
        ->orderBy('agency.created_at', 'desc')
        ->limit(5)
        ->get();

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

        $select = [
            DB::raw('IFNULL(logistic_realization_items.id, needs.id) as id'),
            'needs.id as need_id',
            'logistic_realization_items.id as realization_id',
            DB::raw('IFNULL(logistic_realization_items.product_id, needs.product_id) as product_id'),
            'needs.product_id as need_product_id',
            'logistic_realization_items.product_id as realization_product_id',
            DB::raw('IFNULL(logistic_realization_items.product_name, products.name) as product_name'),
            'products.name as need_product_name',
            'logistic_realization_items.product_name as realization_product_name',
            'needs.brand as need_description',
            DB::raw('IFNULL(logistic_realization_items.realization_quantity, needs.quantity) as quantity'),
            DB::raw('IFNULL(logistic_realization_items.realization_unit, master_unit.unit) as unit_name'),
            'needs.quantity as need_quantity',
            'needs.unit as need_unit_id',
            'master_unit.unit as need_unit_name',
            'needs.usage as need_usage',
            'products.category',

            'logistic_realization_items.realization_quantity as allocation_quantity',
            'logistic_realization_items.created_at as allocated_at',
            
            'logistic_realization_items.realization_quantity',
            'realization_unit as realization_unit_name',
            'logistic_realization_items.created_at as realized_at',
            DB::raw('IFNULL(logistic_realization_items.status, "not_approved") as status')
        ]; 
        
        //List of item(s) added from admin
        $logisticRealizationItems = LogisticRealizationItems::select($select)        
        ->join(
            'needs', 
            'logistic_realization_items.need_id', '=', 'needs.id', 
            'left'
        )
        ->join(
            'products', 
            'needs.product_id', '=', 'products.id', 
            'left'
        )
        ->join(
            'master_unit', 
            'needs.unit', '=', 'master_unit.id', 
            'left'
        )
        ->join(
            'wms_jabar_material', 
            'logistic_realization_items.product_id', '=', 'wms_jabar_material.material_id', 
            'left'
        )
        ->whereNotNull('logistic_realization_items.created_by')
        ->orderBy('logistic_realization_items.id') 
        ->where('logistic_realization_items.applicant_id', $id);

        //List of updated item(s)
        $data = LogisticRealizationItems::select($select)
        ->join(
            'needs', 
            'logistic_realization_items.need_id', '=', 'needs.id',
            'right'
        )
        ->join(
            'products', 
            'needs.product_id', '=', 'products.id', 
            'left'
        )
        ->join(
            'master_unit', 
            'needs.unit', '=', 'master_unit.id', 
            'left'
        )
        ->join(
            'wms_jabar_material', 
            'logistic_realization_items.product_id', '=', 'wms_jabar_material.material_id', 
            'left'
        )
        ->orderBy('needs.id')
        ->union($logisticRealizationItems)
        ->where('needs.applicant_id', $id);
        $data = $data->paginate($limit);

        return response()->format(200, 'success', $data);
    }
}
