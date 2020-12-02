<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\FileUpload;
use App\Agency;
use App\Needs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\LogisticEmailNotification;
use App\User;
use App\Notifications\ChangeStatusNotification;
use JWTAuth;
use App\Applicant;
use App\LogisticRealizationItems;
use App\Validation;
use DB;
use App\Http\Resources\LogisticRequestResource;

class LogisticRequest extends Model
{
    static function responseDataStore()
    {
        return [
            'agency' => null,
            'applicant' => null,
            'applicant_file' => null,
            'need' => null,
            'letter' => null,
        ];
    }

    static function setParamStore()
    {
        return [
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
    }

    static function storeProcess(Request $request)
    {
        $response = Validation::defaultError();
        DB::beginTransaction();
        try {
            $responseData['agency'] = LogisticRequest::agencyStore($request);
            $request->request->add(['agency_id' => $responseData['agency']->id]);
            
            $responseData['applicant'] = Applicant::applicantStore($request);
            $request->request->add(['applicant_id' => $responseData['applicant']->id]);
            
            if ($request->hasFile('applicant_file')) {
                $responseData['applicant_file'] = FileUpload::storeApplicantFile($request);
                $responseData['applicant']->file = $responseData['applicant_file']->id;
                $updateFile = Applicant::where('id', '=', $responseData['applicant']->id)->update(['file' => $responseData['applicant_file']->id]);
            }
            $responseData['need'] = LogisticRequest::needStore($request);
            
            if ($request->hasFile('letter_file')) {
                $responseData['letter'] = FileUpload::storeLetterFile($request);
            }
            $email = LogisticRequest::sendEmailNotification($responseData['agency']->id, Applicant::STATUS_NOT_VERIFIED);
            $whatsapp = LogisticRequest::sendWhatsappNotification($request, 'surat');
            DB::commit();
            $response = response()->format(200, 'success', new LogisticRequestResource($responseData));
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(400, $exception->getMessage(), $responseData);
        }
        return $response;
    }

    static function agencyStore(Request $request)
    {
        $request['location_address'] = $request->input('location_address') == 'undefined' ? '' : $request->input('location_address', '');
        $agency = Agency::create($request->all());
        return $agency;
    }

    static function needStore(Request $request)
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

    static function sendEmailNotification($agencyId, $status)
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

    static function sendWhatsappNotification($request, $phase)
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

    static function setRequestApplicant(Request $request)
    {
        $request['email'] = (!$request->input('email')) ? '' : $request->input('email', '');
        $request['applicants_office'] = (!$request->input('applicants_office')) ? '' : $request->input('applicants_office', '');
        if ($request->hasFile('applicant_file')) {
            $response = FileUpload::storeApplicantFile($request);
            $request['file'] = $response->id;
        }
        return $request;
    }

    static function setRequestEditLetter(Request $request, $id)
    {
        if ($request->hasFile('letter_file')) { //20
            $request['agency_id'] = $id;
            $response = FileUpload::storeLetterFile($request);
        }
        return $request;
    }

    static function saveData(Request $request, $id)
    {
        switch ($request->update_type) {
            case 1:
                $model = Agency::findOrFail($id);
                $request['agency_name'] = MasterFaskes::getFaskesName($request);
                break;
            case 2:
                $model = Applicant::findOrFail($id);
                $request = LogisticRequest::setRequestApplicant($request);
                break;
            case 3:
                $model = Applicant::findOrFail($id);
                $request = LogisticRequest::setRequestEditLetter($request, $id);
                break;
            default:
                $model = Agency::findOrFail($id);
                $request['agency_name'] = MasterFaskes::getFaskesName($request);
                break;
        }
        unset($request['id']);
        $model->fill($request->all());
        $model->save();
        return response()->format(200, 'success');
    }

    static function verificationProcess(Request $request)
    {        
        $response = Validation::defaultError();
        $request['verified_by'] = JWTAuth::user()->id;
        $request['verified_at'] = date('Y-m-d H:i:s');
        $applicant = Applicant::updateApplicant($request);
        $email = LogisticRequest::sendEmailNotification($applicant->agency_id, $request->verification_status);
        if ($request->verification_status !== Applicant::STATUS_REJECTED) {
            $request['agency_id'] = $applicant->agency_id;
            $whatsapp = LogisticRequest::sendEmailNotification($request, 'rekomendasi');
        }
        $response = response()->format(200, 'success', $applicant);
        return $response;
    }

    static function approvalProcess(Request $request)
    {
        $response = Validation::defaultError();
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
            $email = LogisticRequest::sendEmailNotification($applicant->agency_id, $request->approval_status);
            if ($request->approval_status === Applicant::STATUS_APPROVED) {
                $request['agency_id'] = $applicant->agency_id;
                $whatsapp = LogisticRequest::sendEmailNotification($request, 'realisasi');
            }
            $response = response()->format(200, 'success', $applicant);
        }
        return $response;
    }

    static function finalProcess(Request $request)
    {
        $response = Validation::defaultError();
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
            $email = LogisticRequest::sendEmailNotification($applicant->agency_id, $request->approval_status);
            $response = response()->format(200, 'success', [
                '(needsSum_realization_sum' => ($needsSum + $realizationSum),
                'finalSum' => $finalSum,
                'total_item_need_update' => (($needsSum + $realizationSum) - $finalSum)
            ]);
        }
    }

    
}
