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
use App\Tracking;

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

    public function finalList(Request $request)
    {
        $logisticRequest = Agency::getList($request, false)
        ->join('applicants', 'agency.id', '=', 'applicants.agency_id')
        ->where('is_deleted', '!=' , 1)
        ->where('applicants.verification_status', Applicant::STATUS_VERIFIED)
        ->where('applicants.approval_status', Applicant::STATUS_APPROVED)
        ->whereNotNull('applicants.finalized_by');
        if ($request->filled('is_integrated')) {
            $logisticRequest = $logisticRequest->where('is_integrated', '=', $request->input('is_integrated'));
        }
        $logisticRequest = $logisticRequest->get();

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
        $param = LogisticRequest::setParamStore();
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $response = LogisticRequest::storeProcess($request);
        }
        return $response;
    }

    public function update(Request $request, $id)
    {
        $param['agency_id'] = 'required';
        $param['applicant_id'] = 'required';
        $param['update_type'] = 'required';
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $response = LogisticRequest::saveData($request, $id);
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
        return $response;
    }

    public function requestSummary(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->input('start_date') . ' 00:00:00' : '2020-01-01 00:00:00';
        $endDate = $request->filled('end_date') ? $request->input('end_date') . ' 23:59:59' : date('Y-m-d H:i:s');

        $requestSummaryResult['lastUpdate'] = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => false, 'verification_status' => false])->orderBy('updated_at', 'desc')->first();
        $requestSummaryResult['totalPikobar'] = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => 'pikobar', 'approval_status' => false, 'verification_status' => false])->count();
        $requestSummaryResult['totalDinkesprov'] = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => 'dinkes_provinsi', 'approval_status' => false, 'verification_status' => false])->count();
        $requestSummaryResult['totalUnverified'] = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => Applicant::STATUS_NOT_APPROVED, 'verification_status' => Applicant::STATUS_NOT_VERIFIED])->count();
        $requestSummaryResult['totalApproved'] = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => Applicant::STATUS_APPROVED, 'verification_status' => Applicant::STATUS_VERIFIED])->whereNull('finalized_by')->count();
        $requestSummaryResult['totalFinal'] = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => Applicant::STATUS_APPROVED, 'verification_status' => Applicant::STATUS_VERIFIED])->whereNotNull('finalized_by')->count();
        $requestSummaryResult['totalVerified'] = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => Applicant::STATUS_NOT_APPROVED, 'verification_status' => Applicant::STATUS_VERIFIED])->count();
        $requestSummaryResult['totalVerificationRejected'] = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => Applicant::STATUS_NOT_APPROVED, 'verification_status' => Applicant::STATUS_REJECTED])->count();
        $requestSummaryResult['totalApprovalRejected'] = Applicant::getTotalBy([$startDate, $endDate], ['source_data' => false, 'approval_status' => Applicant::STATUS_REJECTED, 'verification_status' => Applicant::STATUS_VERIFIED])->count();
        
        $data = Applicant::requestSummaryResult($requestSummaryResult);
        return response()->format(200, 'success', $data);
    }

    public function changeStatus(Request $request)
    {
        $param['applicant_id'] = 'required|numeric';
        $processType = 'verification';
        $changeStatusParam = $this->setChangeStatusParam($request, $param, $processType);
        $param = $changeStatusParam['param'];
        $processType = $changeStatusParam['processType'];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $response = LogisticRequest::changeStatus($request, $processType);
        }
        return $response;
    }

    public function setChangeStatusParam(Request $request, $param, $processType)
    {
        if ($request->route()->named('verification')) {
            $processType = 'verification';
            $param['verification_status'] = 'required|string';
            $param['note'] = $request->verification_status === Applicant::STATUS_REJECTED ? 'required' : '';
        } else if ($request->route()->named('approval')) {
            $processType = 'approval';
            $param['approval_status'] = 'required|string';
            $param['approval_note'] = $request->approval_status === Applicant::STATUS_REJECTED ? 'required' : '';
        } else {
            $processType = 'final';
            $param['approval_status'] = 'required|string';
            $param['approval_note'] = $request->approval_status === Applicant::STATUS_REJECTED ? 'required' : '';
        }

        $changeStatusParam['param'] = $param;
        $changeStatusParam['processType'] = $processType;

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

    /**
     * Track Function
     * Show application list based on ID, No. HP, or applicant email
     * @param Request $request
     * @return array of Applicant $data
     */
    public function track(Request $request)
    {
        $list = Tracking::trackList($request);
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
        $select = Tracking::selectFieldsDetail();
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
                $masterFaskes = MasterFaskes::createFaskes($request);
                $request['master_faskes_id'] = $masterFaskes->id;
                $response = $request;
            }
        }
        return $response;
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
            $whatsapp = LogisticRequest::sendEmailNotification($request, $request['status']);
            $response = response()->format(200, 'success', $request->all());
        }
        return $response;
    }
}
