<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Needs;
use App\Agency;
use App\Applicant;
use App\FileUpload;
use App\Http\Resources\LogisticRequestResource;
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
use App\Notifications\ChangeStatusNotification;
use App\User;

class LogisticRequestController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->filled('limit') ? $request->input('limit') : 10;
        $sort = $request->filled('sort') ? ['agency_name ' . $request->input('sort') . ', ', 'updated_at DESC'] : ['updated_at DESC, ', 'agency_name ASC'];
        $data = Agency::getList($request, false);        
        $data = $data->orderByRaw(implode($sort))->paginate($limit);
        Validation::completenessDetail($data);
        return response()->format(200, 'success', $data);
    }

    public function store(Request $request)
    {
        $request = $this->masterFaskesCheck($request);
        $responseData = [
            'agency' => null,
            'applicant' => null,
            'applicant_file' => null,
            'need' => null,
            'letter' => null,
        ];
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
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            DB::beginTransaction();
            try {
                $responseData['agency'] = $this->agencyStore($request);
                $request->request->add(['agency_id' => $responseData['agency']->id]);
                
                $responseData['applicant'] = Applicant::applicantStore($request);
                $request->request->add(['applicant_id' => $responseData['applicant']->id]);
                
                if ($request->hasFile('applicant_file')) {
                    $responseData['applicant_file'] = FileUpload::storeApplicantFile($request);
                    $responseData['applicant']->file = $responseData['applicant_file']->id;
                    $updateFile = Applicant::where('id', '=', $responseData['applicant']->id)->update(['file' => $responseData['applicant_file']->id]);
                }
                $responseData['need'] = $this->needStore($request);
                
                if ($request->hasFile('letter_file')) {
                    $responseData['letter'] = FileUpload::storeLetterFile($request);
                }
                $email = $this->sendEmailNotification($responseData['agency']->id, Applicant::STATUS_NOT_VERIFIED);
                $whatsapp = $this->sendWhatsappNotification($request, 'surat');
                DB::commit();
                $response = response()->format(200, 'success', new LogisticRequestResource($responseData));
            } catch (\Exception $exception) {
                DB::rollBack();
                $response = response()->format(400, $exception->getMessage(), $responseData);
            }
        }
        return $response;
    }

    public function update(Request $request, $id)
    {
        $param = [
            'update_type' => 'required'
        ];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            try {
                switch ($request->update_type) {
                    case 1:
                        $model = Agency::findOrFail($id);
                        $request['agency_name'] = MasterFaskes::getFaskesName($request);
                        break;
                    case 2:
                        $model = Applicant::findOrFail($id);
                        $request['email'] = (!$request->input('email')) ? '' : $request->input('email', '');
                        $request['applicants_office'] = (!$request->input('applicants_office')) ? '' : $request->input('applicants_office', '');
                        if ($request->hasFile('applicant_file')) {
                            $response = FileUpload::storeApplicantFile($request);
                            $request['file'] = $response->id;
                        }
                        break;
                    case 3:
                        $model = Applicant::findOrFail($id);
                        if ($request->hasFile('letter_file')) {
                            $request['agency_id'] = $id;
                            $response = FileUpload::storeLetterFile($request);
                        }
                        break;
                    default:
                        $model = Agency::findOrFail($id);
                        $request['agency_name'] = MasterFaskes::getFaskesName($request);
                        break;
                }
                unset($request['id']);
                $model->fill($request->all());
                $model->save();
                $response = response()->format(200, 'success');
            } catch (\Exception $exception) {
                $response = response()->format(400, $exception->getMessage());
            }
        }
        return $response;
    }

    public function agencyStore($request)
    {
        $request['location_address'] = $request->input('location_address') == 'undefined' ? '' : $request->input('location_address', '');
        $agency = Agency::create($request->all());
        return $agency;
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
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $request['verified_by'] = JWTAuth::user()->id;
            $request['verified_at'] = date('Y-m-d H:i:s');
            $applicant = Applicant::updateApplicant($request);
            $email = $this->sendEmailNotification($applicant->agency_id, $request->verification_status);
            if ($request->verification_status !== Applicant::STATUS_REJECTED) {
                $request['agency_id'] = $applicant->agency_id;
                $whatsapp = $this->sendWhatsappNotification($request, 'rekomendasi');
            }
            $response = response()->format(200, 'success', $applicant);
        }
        return $response;
    }

    public function listNeed(Request $request)
    {
        $param = ['agency_id' => 'required'];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
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
            $response = response()->format(200, 'success', $data);
        }
        return $response;
    }

    public function import(Request $request)
    {
        $param = ['file' => 'required|mimes:xlsx'];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            DB::beginTransaction();
            try {
                $import = new MultipleSheetImport();
                $ts = Excel::import($import, request()->file('file'));
                LogisticImport::import($import);
                DB::commit();
                $response = response()->format(200, 'success', '');
            } catch (\Exception $exception) {
                DB::rollBack();
                $response = response()->format(400, $exception->getMessage());
            }
        }
        return $response;
    }

    public function requestSummary(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->input('start_date') . ' 00:00:00' : '2020-01-01 00:00:00';
        $endDate = $request->filled('end_date') ? $request->input('end_date') . ' 23:59:59' : date('Y-m-d H:i:s');
        try {
            $data = Applicant::getTotal($request, $startDate, $endDate);
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

    public function sendWhatsappNotification($request, $phase)
    {
        $requiredData = [
            'phase' => $phase,
            'id' => $request['agency_id'],
            'url' => $request['url'],
        ];
        $users = User::where('phase', $phase)->where('handphone', '!=', '')->get();
        foreach ($users as $user) {
            $notify[] = $user->notify(new ChangeStatusNotification($requiredData));
        }
    }

    public function approval(Request $request)
    {
        $param = [
            'applicant_id' => 'required|numeric',
            'approval_status' => 'required|string'
        ];
        $param['approval_note'] = $request->approval_status === Applicant::STATUS_REJECTED ? 'required' : '';
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
             // check the list of applications that have not been approved
            $needsSum = Needs::where('applicant_id', $request->applicant_id)->count();
            $realizationSum = LogisticRealizationItems::where('applicant_id', $request->applicant_id)->whereNull('created_by')->count();
            if ($realizationSum != $needsSum && $request->approval_status === Applicant::STATUS_APPROVED) {
                $message = 'Sebelum melakukan persetujuan permohonan, pastikan item barang sudah diupdate terlebih dahulu. Jumlah barang yang belum diupdate sebanyak ' . ($needsSum - $realizationSum) .' item';
                $response = response()->json([
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
                if ($request->approval_status === Applicant::STATUS_APPROVED) {
                    $request['agency_id'] = $applicant->agency_id;
                    $whatsapp = $this->sendWhatsappNotification($request, 'realisasi');
                }
                $response = response()->format(200, 'success', $applicant);
            }
        }
        return $response;
    }

    public function final(Request $request)
    {
        $param = [
            'applicant_id' => 'required|numeric',
            'approval_status' => 'required|string'
        ];
        $param['approval_note'] = $request->approval_status === Applicant::STATUS_REJECTED ? 'required' : '';
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            //check the list of applications that have not been approved
            $needsSum = Needs::where('applicant_id', $request->applicant_id)->count();
            $realizationSum = LogisticRealizationItems::where('applicant_id', $request->applicant_id)->whereNotNull('created_by')->count();
            $finalSum = LogisticRealizationItems::where('applicant_id', $request->applicant_id)->whereNotNull('final_by')->count();
            
            if ($finalSum != ($needsSum + $realizationSum) && $request->approval_status === Applicant::STATUS_APPROVED) {
                $message = 'Sebelum menyelesaikan permohonan, pastikan item barang sudah diupdate terlebih dahulu. Jumlah barang yang belum diupdate sebanyak ' . (($needsSum + $realizationSum) - $finalSum) .' item';
                $response = response()->json([
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
                $response = response()->format(200, 'success', [
                    '(needsSum_realization_sum' => ($needsSum + $realizationSum),
                    'finalSum' => $finalSum,
                    'total_item_need_update' => (($needsSum + $realizationSum) - $finalSum)
                ]);
            }
        }
        return $response;
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
        $data = $data->union($logisticRealizationItems)->paginate($limit);
        return response()->format(200, 'success', $data);
    }

    public function masterFaskesCheck($request)
    {
        return $request = (!MasterFaskes::find($request->master_faskes_id)) ? $this->alloableAgencyType($request) : $request;
    }

    public function alloableAgencyType($request)
    {
        $response = Validation::validateAgencyType($request->agency_type, ['4', '5']);
        if ($response->getStatusCode() === 200) {
            $param = [
                'agency_type' => 'required|numeric',
                'agency_name' => 'required|string'
            ];
            $response = Validation::validate($request, $param);
            if ($response->getStatusCode() === 200) {
                $masterFaskes = $this->createFaskes($request);
                $request['master_faskes_id'] = $masterFaskes->id;
                $response = $request;
            }
        }
        return $response;
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

    public function uploadLetter(Request $request, $id)
    {
        $param = [
            'letter_file' => 'required|mimes:jpeg,jpg,png,pdf|max:10240'
        ];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $applicant = Applicant::findOrFail($id);
            $request->request->add(['agency_id' => $applicant->id]);
            $request->request->add(['applicant_id' => $applicant->id]);
            $response = FileUpload::storeLetterFile($request);
        }
        return $response;
    }

    public function uploadApplicantFile(Request $request, $id)
    {
        $param = [
            'applicant_file' => 'required|mimes:jpeg,jpg,png,pdf|max:10240'
        ];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $request->request->add(['applicant_id' => $id]);
            $response = FileUpload::storeApplicantFile($request);        
            $applicant = Applicant::where('id', '=', $request->applicant_id)->update(['file' => $response->id]);
        }
        return $response;
    }

    public function urgencyChange(Request $request)
    {
        $param = [
            'id' => 'required|numeric',
            'is_urgency' => 'required|numeric',
        ];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $model = Applicant::findOrFail($request->id);
            $model->is_urgency = $request->is_urgency;
            $model->save();
            $response = response()->format(200, 'success', $model);
        }
        return $response;
    }

    public function undoStep(Request $request)
    {
        $param = [
            'id' => 'required|numeric',
            'step' => 'required',
            'url' => 'required'
        ];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $request = Applicant::undoStep($request);
            $request['agency_id'] = $request->id;
            $whatsapp = $this->sendWhatsappNotification($request, $request['status']);
            $response = response()->format(200, 'success', $request->all());
        }
        return $response;
    }
}
