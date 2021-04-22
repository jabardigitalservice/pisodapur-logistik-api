<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Needs;
use App\Agency;
use App\Applicant;
use App\LogisticRequest;
use App\FileUpload;
use App\Imports\LogisticImport;
use Maatwebsite\Excel\Facades\Excel;
use App\MasterFaskes;
use App\Validation;
use Log;

class LogisticRequestController extends Controller
{
    public function index(Request $request)
    {
        $request->start_date = $request->filled('start_date') ? $request->input('start_date') . ' 00:00:00' : '2020-01-01 00:00:00';
        $request->end_date = $request->filled('end_date') ? $request->input('end_date') . ' 23:59:59' : date('Y-m-d H:i:s');

        $limit = $request->input('limit', 10);
        $sort = $request->filled('sort') ? ['agency_name ' . $request->input('sort') . ', ', 'updated_at DESC'] : ['updated_at DESC, ', 'agency_name ASC'];
        $data = Agency::getList($request, false);
        $data = $data->orderByRaw(implode($sort))->paginate($limit);
        return response()->format(200, 'success', $data);
    }

    public function finalList(Request $request)
    {
        $syncSohLocation = \App\PoslogProduct::syncSohLocation();
        $request->request->add(['verification_status' => Applicant::STATUS_VERIFIED]);
        $request->request->add(['approval_status' => Applicant::STATUS_APPROVED]);
        $request->request->add(['finalized_by' => Applicant::STATUS_FINALIZED]);
        // Cut Off Logistic Data
        $cutOffDateTimeState = \Carbon\Carbon::createFromFormat(config('wmsjabar.cut_off_format'), config('wmsjabar.cut_off_datetime'))->toDateTimeString();
        $cutOffDateTime = $request->input('cut_off_datetime', $cutOffDateTimeState);
        $today = \Carbon\Carbon::now()->toDateTimeString();

        $request->request->add(['start_date' => $cutOffDateTime]);
        $request->request->add(['end_date' => $today]);
        $logisticRequest = Agency::getList($request, false)->get();

        $data = [
            'data' => $logisticRequest,
            'total' => count($logisticRequest)
        ];
        return response()->format(200, 'success', $data);
    }

    public function store(Request $request)
    {
        $request = $this->masterFaskesCheck($request);
        $responseData = LogisticRequest::responseDataStore();
        $param = LogisticRequest::setParamStore($request);
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $response = LogisticRequest::storeProcess($request, $responseData);
            Validation::setCompleteness($request);
        }
        Log::channel('dblogging')->debug('post:v1/logistic-request', $request->all());
        return $response;
    }

    public function update(Request $request, $id)
    {
        $param['agency_id'] = 'required';
        $param['applicant_id'] = 'required';
        $param['update_type'] = 'required';
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $response = LogisticRequest::saveData($request);
            Validation::setCompleteness($request);
        }
        Log::channel('dblogging')->debug('put:v1/logistic-request', $request->all());
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

    public function listNeed(Request $request)
    {
        $param = ['agency_id' => 'required'];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $response = Needs::listNeed($request);
        }
        return $response;
    }

    public function import(Request $request)
    {
        $param = ['file' => 'required|mimes:xlsx'];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $response = LogisticImport::importProcess($request);
        }
        Log::channel('dblogging')->debug('post:v1/logistic-request/import', $request->all());
        return $response;
    }

    public function requestSummary(Request $request)
    {
        $startDate = $request->has('start_date') ? $request->input('start_date') . ' 00:00:00' : '2020-01-01 00:00:00';
        $endDate = $request->has('end_date') ? $request->input('end_date') . ' 23:59:59' : date('Y-m-d H:i:s');

        $lastUpdate = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => false, 'verification_status' => false])->orderBy('updated_at', 'desc');
        $totalPikobar = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => 'pikobar', 'approval_status' => false, 'verification_status' => false]);
        $totalDinkesprov = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => 'dinkes_provinsi', 'approval_status' => false, 'verification_status' => false]);
        $totalUnverified = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => Applicant::STATUS_NOT_APPROVED, 'verification_status' => Applicant::STATUS_NOT_VERIFIED]);
        $totalApproved = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => Applicant::STATUS_APPROVED, 'verification_status' => Applicant::STATUS_VERIFIED])->whereNull('finalized_by');
        $totalFinal = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => Applicant::STATUS_APPROVED, 'verification_status' => Applicant::STATUS_VERIFIED])->whereNotNull('finalized_by');
        $totalVerified = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => Applicant::STATUS_NOT_APPROVED, 'verification_status' => Applicant::STATUS_VERIFIED]);
        $totalVerificationRejected = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => Applicant::STATUS_NOT_APPROVED, 'verification_status' => Applicant::STATUS_REJECTED]);
        $totalApprovalRejected = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => Applicant::STATUS_REJECTED, 'verification_status' => Applicant::STATUS_VERIFIED]);

        if ($request->has('city_code')) {
            $lastUpdate->filterByCity($request->input('city_code'));
            $totalPikobar->filterByCity($request->input('city_code'));
            $totalDinkesprov->filterByCity($request->input('city_code'));
            $totalUnverified->filterByCity($request->input('city_code'));
            $totalApproved->filterByCity($request->input('city_code'));
            $totalFinal->filterByCity($request->input('city_code'));
            $totalVerified->filterByCity($request->input('city_code'));
            $totalVerificationRejected->filterByCity($request->input('city_code'));
            $totalApprovalRejected->filterByCity($request->input('city_code'));
        }

        $requestSummaryResult['lastUpdate'] = $lastUpdate->first();
        $requestSummaryResult['totalPikobar'] = $totalPikobar->count();
        $requestSummaryResult['totalDinkesprov'] = $totalDinkesprov->count();
        $requestSummaryResult['totalUnverified'] = $totalUnverified->count();
        $requestSummaryResult['totalApproved'] = $totalApproved->count();
        $requestSummaryResult['totalFinal'] = $totalFinal->count();
        $requestSummaryResult['totalVerified'] = $totalVerified->count();
        $requestSummaryResult['totalVerificationRejected'] = $totalVerificationRejected->count();
        $requestSummaryResult['totalApprovalRejected'] = $totalApprovalRejected->count();

        $data = Applicant::requestSummaryResult($requestSummaryResult);
        return response()->format(200, 'success', $data);
    }

    public function changeStatus(Request $request)
    {
        $param['agency_id'] = 'required|numeric';
        $param['applicant_id'] = 'required|numeric';
        $processType = 'verification';
        $changeStatusParam = $this->setChangeStatusParam($request, $param, $processType);
        $param = $changeStatusParam['param'];
        $processType = $changeStatusParam['processType'];
        $dataUpdate = $changeStatusParam['dataUpdate'];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $response = LogisticRequest::changeStatus($request, $processType, $dataUpdate);
            Validation::setCompleteness($request);
        }

        Log::channel('dblogging')->debug('post:v1/logistic-request/' . $processType, $request->all());
        return $response;
    }

    public function setChangeStatusParam(Request $request, $param, $processType)
    {
        $dataUpdate = [];
        if ($request->route()->named('verification')) {
            $processType = 'verification';
            $param['verification_status'] = 'required|string';
            $param['note'] = $request->verification_status === Applicant::STATUS_REJECTED ? 'required' : '';
            $dataUpdate['verification_status'] = $request->verification_status;
            $dataUpdate['note'] = $request->verification_status === Applicant::STATUS_REJECTED ? $request->note : '';
        } else if ($request->route()->named('approval')) {
            $processType = 'approval';
            $param['approval_status'] = 'required|string';
            $param['approval_note'] = $request->approval_status === Applicant::STATUS_REJECTED ? 'required' : '';
            $dataUpdate['approval_status'] = $request->approval_status;
            $dataUpdate['approval_note'] = $request->approval_status === Applicant::STATUS_REJECTED ? $request->approval_note : '';
        } else {
            $processType = 'final';
            $param['approval_status'] = 'required|string';
            $param['approval_note'] = $request->approval_status === Applicant::STATUS_REJECTED ? 'required' : '';
            $dataUpdate['approval_status'] = $request->approval_status;
            $dataUpdate['approval_note'] = $request->approval_status === Applicant::STATUS_REJECTED ? $request->approval_note : '';
        }

        $changeStatusParam['param'] = $param;
        $changeStatusParam['processType'] = $processType;
        $changeStatusParam['dataUpdate'] = $dataUpdate;

        return $changeStatusParam;
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
                $masterFaskes = MasterFaskes::createFaskes($request);
                $request['master_faskes_id'] = $masterFaskes->id;
                $response = $request;
            }
        }
        return $response;
    }

    public function uploadLetter(Request $request, $id)
    {
        $param['letter_file'] = 'required|mimes:jpeg,jpg,png,pdf|max:10240';
        $param['agency_id'] = 'required';
        $param['applicant_id'] = 'required';
        $param['update_type'] = 'required';
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $applicant = Applicant::where('id', $request->applicant_id)->where('agency_id', $request->agency_id)->firstOrFail();
            $response = FileUpload::storeLetterFile($request);
            Validation::setCompleteness($request);
        }
        Log::channel('dblogging')->debug('post:v1/logistic-request/letter/' . $id, $request->all());
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
            Validation::setCompleteness($request);
        }
        Log::channel('dblogging')->debug('post:v1/logistic-request/identity/' . $id, $request->all());
        return $response;
    }

    public function urgencyChange(Request $request)
    {
        $param = [
            'agency_id' => 'required|numeric',
            'applicant_id' => 'required|numeric',
            'is_urgency' => 'required|numeric',
        ];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $model = Applicant::where('id', $request->applicant_id)->where('agency_id', $request->agency_id)->first();
            $model->is_urgency = $request->is_urgency;
            $model->save();
            $response = response()->format(200, 'success', $model);
            Validation::setCompleteness($request);
        }
        Log::channel('dblogging')->debug('post:v1/logistic-request/urgency', $request->all());
        return $response;
    }

    public function undoStep(Request $request)
    {
        $param = [
            'agency_id' => 'required|numeric',
            'applicant_id' => 'required|numeric',
            'step' => 'required',
            'url' => 'required'
        ];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $request = Applicant::undoStep($request);
            $email = LogisticRequest::sendEmailNotification($request, $request['status']);
            $response = response()->format(200, 'success', $request->all());
            Validation::setCompleteness($request);
        }
        Log::channel('dblogging')->debug('post:v1/logistic-request/return', $request->all());
        return $response;
    }
}
