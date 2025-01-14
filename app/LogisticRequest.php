<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\LogisticEmailNotification;
use App\Notifications\ChangeStatusNotification;
use App\Applicant;
use App\LogisticRealizationItems;
use App\Validation;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\LogisticRequestResource;
use Illuminate\Http\Response;

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

    static function setParamStore($request)
    {
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
            'letter_file' => 'required|file|max:10240',
            'application_letter_number' => 'required|string'
        ];

        $agencyTypeExcept = [1, 2, 3];
        if (in_array($request->agency_type, $agencyTypeExcept)) {
            $param['total_covid_patients'] = 'required|numeric';
            $param['total_isolation_room'] = 'required|numeric';
            $param['total_bedroom'] = 'required|numeric';
            $param['total_health_worker'] = 'required|numeric';
        }

        return $param;
    }

    static function storeProcess(Request $request, $responseData)
    {
        $response = Validation::defaultError();
        DB::beginTransaction();
        try {
            $responseData['agency'] = LogisticRequest::agencyStore($request);
            $request->merge(['agency_id' => $responseData['agency']->id]);

            $responseData['applicant'] = Applicant::applicantStore($request);
            $request->merge(['applicant_id' => $responseData['applicant']->id]);

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
            $response = response()->format(Response::HTTP_OK, 'success', new LogisticRequestResource($responseData));
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage(), $exception->getTrace());
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
                'brand' => $value['description'],
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
                ])->where('is_deleted', '!=', 1);
            }])->findOrFail($agencyId);
            Mail::to($agency->applicant['email'])->send(new LogisticEmailNotification($agency, $status));
        } catch (\Exception $exception) {
            return response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage());
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

    static function setRequestEditLetter(Request $request)
    {
        if ($request->hasFile('letter_file')) {
            $response = FileUpload::storeLetterFile($request);
        }
        return $request;
    }

    static function saveData(Request $request)
    {
        switch ($request->update_type) {
            case 1:
                $model = Agency::findOrFail($request->agency_id);
                $request['agency_name'] = MasterFaskes::getFaskesName($request);
                break;
            case 2:
                $model = Applicant::where('id', $request->applicant_id)->where('agency_id', $request->agency_id)->firstOrFail();
                $request = LogisticRequest::setRequestApplicant($request);
                break;
            case 3:
                $model = Applicant::where('id', $request->applicant_id)->where('agency_id', $request->agency_id)->firstOrFail();
                $request = LogisticRequest::setRequestEditLetter($request);
                break;
        }
        unset($request['id']);
        $model->fill($request->all());
        $model->save();
        return response()->format(Response::HTTP_OK, 'success');
    }

    static function changeStatus(Request $request, $processType, $dataUpdate)
    {
        switch ($processType) {
            case 'verification':
                $response = LogisticRequest::verificationProcess($request, $dataUpdate);
                break;
            case 'approval':
                $param = LogisticRequest::setParam($request, $processType);
                $response = LogisticRequest::getResponseApproval($request, $param, $dataUpdate);
                break;
            case 'final':
                $param = LogisticRequest::setParam($request, $processType);
                $response = LogisticRequest::finalProcess($request, $param);
                break;
        }
        return $response;
    }

    static function verificationProcess(Request $request, $dataUpdate)
    {
        $response = Validation::defaultError();
        $dataUpdate['verified_by'] = auth()->user()->id;
        $dataUpdate['verified_at'] = date('Y-m-d H:i:s');
        $applicant = Applicant::updateApplicant($request, $dataUpdate);
        $email = LogisticRequest::sendEmailNotification($applicant->agency_id, $request->verification_status);
        if ($request->verification_status !== Applicant::STATUS_REJECTED) {
            $whatsapp = LogisticRequest::sendWhatsappNotification($request, 'rekomendasi');
        }
        $response = response()->format(Response::HTTP_OK, 'success', $applicant);
        return $response;
    }

    static function finalProcess(Request $request, $param)
    {
        $response = LogisticRequest::getResponseApproval($request, $param);
        // handling integration to poslog
        if ($response->getStatusCode() == Response::HTTP_OK) {
            $response = WmsJabar::sendRequest($request);
        }
        return $response;
    }

    static function getResponseApproval(Request $request, $param, $dataUpdate = [])
    {
        $response = response()->json([
            'status' => 422,
            'error' => true,
            'message' => $param['failMessage'],
            'total_item_need_update' => $param['notReadyItemsTotal'],
            'param' => $param,
        ], 422);
        if (!$param['checkAllItemsStatus']) {
            if ($param['step'] != 'finalized') {
                $dataUpdate[$param['step'] . '_by'] = auth()->user()->id;
                $dataUpdate[$param['step'] . '_at'] = date('Y-m-d H:i:s');
            }
            $applicant = Applicant::updateApplicant($request, $dataUpdate);
            // Send Notification Email
            $email = LogisticRequest::sendEmailNotification($applicant->agency_id, $param['applicantStatus']);
            // Send Whatsapp Notification to PIC
            $isApproved = $request->approval_status === Applicant::STATUS_APPROVED;
            $isStepApproved = $param['step'] == 'approved';
            if ($isApproved && $isStepApproved) {
                $request['agency_id'] = $applicant->agency_id;
                $whatsapp = LogisticRequest::sendWhatsappNotification($request, $param['phase']);
            }
            $response = response()->format(Response::HTTP_OK, 'success', $applicant);
        }
        return $response;
    }

    static function setParam($request, $phase)
    {
        $param['needsSum'] = Needs::where('applicant_id', $request->applicant_id)->count(); // total item from user request
        $param['realizationSum'] = LogisticRealizationItems::where('applicant_id', $request->applicant_id)->whereNotNull('need_id')->count(); // total item from admin recommendation
        $param['applicantStatus'] = $request->approval_status;
        $param['checkAllItemsStatus'] = $param['realizationSum'] != $param['needsSum'] && $request->approval_status === Applicant::STATUS_APPROVED;
        $param['notReadyItemsTotal'] = $param['needsSum'] - $param['realizationSum'];
        $param['step'] = 'approved';
        $param['phase'] = 'realisasi';
        if ($phase == 'final') {
            $param['applicantStatus'] = Applicant::STATUS_FINALIZED;
            $param['realizationSumFinal'] = LogisticRealizationItems::where('applicant_id', $request->applicant_id)->whereNotNull('created_by')->count();
            $param['recommendationItemsTotal'] = $param['needsSum'] + $param['realizationSumFinal'];
            $param['finalSumByNeeds'] = LogisticRealizationItems::where('applicant_id', $request->applicant_id)->whereNotNull('need_id')->whereNotNull('final_by')->count(); // total final item from user Request
            $param['finalSumByAdmin'] = LogisticRealizationItems::where('applicant_id', $request->applicant_id)->whereNotNull('created_by')->whereNotNull('final_by')->count(); // total final item from Admin Recommendation
            $param['finalSum'] = $param['finalSumByNeeds'] + $param['finalSumByAdmin'];
            $param['checkAllItemsStatus'] = ($param['finalSum'] != $param['recommendationItemsTotal']) && ($request->approval_status === Applicant::STATUS_APPROVED);
            $param['notReadyItemsTotal'] = $param['recommendationItemsTotal'] - $param['finalSum'];
            $param['step'] = 'finalized';
            $param['phase'] = 'final';
        }
        $param['failMessage'] = 'Sebelum menyelesaikan permohonan, pastikan item barang sudah diupdate terlebih dahulu. Jumlah barang yang belum diupdate sebanyak ' . $param['notReadyItemsTotal'] . ' item';

        return $param;
    }
}
