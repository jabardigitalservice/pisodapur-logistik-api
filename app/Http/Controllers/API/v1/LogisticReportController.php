<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Validation;
use App\LogisticVerification;
use App\Applicant;
use DB;
use App\LogisticRealizationItems;
use Illuminate\Support\Facades\Mail;
use App\Mail\TokenEmailNotification;
use App\AcceptanceReport;
use App\AcceptanceReportDetail;
use App\FileUpload;

class LogisticReportController extends Controller
{
    
    public function verificationRegistration(Request $request)
    {
        $param['register_id'] = 'required|numeric';
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $request->id = $request->register_id;
            try {
                $applicant = Applicant::where('agency_id', $request->id)->firstOrFail();
                $logisticVerification = LogisticVerification::firstOrCreate(['agency_id' => $request->id], ['email' => $applicant->email]);
                $logisticVerification = $this->sendEmailCondition($logisticVerification);
                $response = response()->format(200, 'success', $logisticVerification);
            } catch (\Exception $exception) {
                $response = response()->format(422, 'Permohonan dengan Kode Permohonan ' . $request->id . ' tidak ditemukan.', $exception);
            }
        }
        return $response;
    }

    public function sendEmailCondition($logisticVerification)
    {
        if ($logisticVerification->expired_at <= date('Y-m-d H:i:s')) {
            // reset token
            $token = rand(10000, 99999);
            $logisticVerification->token = $token;
            $logisticVerification->expired_at = date('Y-m-d H:i:s', strtotime('+1 days'));
            $logisticVerification->save();
            // send email
            Mail::to($logisticVerification->email)->send(new TokenEmailNotification($token));
        }
        return $logisticVerification;
    }

    public function verificationConfirmation(Request $request)
    {
        $param['register_id'] = 'required|numeric';
        $param['verification_code1'] = 'required|numeric';
        $param['verification_code2'] = 'required|numeric';
        $param['verification_code3'] = 'required|numeric';
        $param['verification_code4'] = 'required|numeric';
        $param['verification_code5'] = 'required|numeric';
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            // Confirm Token
            $token = $request->verification_code1 . $request->verification_code2 . $request->verification_code3 . $request->verification_code4 . $request->verification_code5;
            $response = response()->format(422, 'kode verifikasi tidak sesuai');
            $logisticVerification = LogisticVerification::where('agency_id', $request->register_id)->firstOrfail();
            if ($token == $logisticVerification->token) {
                $response = response()->format(200, 'success', ['token' => $token]);
            }
        }
        return $response;
    }

    public function acceptanceStore(Request $request)
    {
        DB::beginTransaction();
        try {
            $acceptanceReport = $this->storeAcceptanceReport($request);
            $this->itemStore($request, $acceptanceReport);
            // Upload Seluruh File
            $proof_pic = $this->uploadAcceptanceFile($request, 'proof_pic');
            $bast_proof = $this->uploadAcceptanceFile($request, 'bast_proof');
            $item_proof = $this->uploadAcceptanceFile($request, 'item_proof');
            DB::commit();
            $response = response()->format(200, 'success');
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(422, 'Terjadi Kesalahan', ['message' => $exception->getMessage(), 'exception' => $exception]);
        }
        return $response;
    }

    public function storeAcceptanceReport($request)
    {
        $acceptanceReport = new AcceptanceReport;
        $acceptanceReport->fullname = $request->fullname;
        $acceptanceReport->position = $request->position;
        $acceptanceReport->phone = $request->phone;
        $acceptanceReport->date = $request->date;
        $acceptanceReport->officer_fullname = $request->officer_fullname;
        $acceptanceReport->note = $request->note;
        $acceptanceReport->agency_id = $request->agency_id;
        $acceptanceReport->save();
        return $acceptanceReport;
    }

    public function itemStore($request, $acceptanceReport)
    {
        foreach (json_decode($request->items, true) as $value) {
            $acceptanceReportDetail = new AcceptanceReportDetail;
            $acceptanceReportDetail->acceptance_report_id = $acceptanceReport->id;
            $acceptanceReportDetail->agency_id = $request->agency_id;
            $acceptanceReportDetail->logistic_realization_item_id = $value['id'];
            $acceptanceReportDetail->product_id = $value['product_id'];
            $acceptanceReportDetail->product_name = $value['name'];
            $acceptanceReportDetail->qty = $value['qty'];
            $acceptanceReportDetail->unit = $value['unit'];
            $acceptanceReportDetail->status = $value['status'];
            $acceptanceReportDetail->qty_ok = $value['qty_ok'];
            $acceptanceReportDetail->qty_nok = $value['qty_nok'];
            $acceptanceReportDetail->quality = $value['quality'];
            $acceptanceReportDetail->save();
        }
    }

    public function uploadAcceptanceFile($request, $paramName)
    {
        $file = [];
        for ($i = 0; $i < $request->input($paramName . '_length'); $i++) {
            if ($request->hasFile($paramName . $i)) {
                $file[] = FileUpload::uploadAcceptanceReportFile($request, $paramName . $i);
            }
        }

        return $file;
    }

    public function realizationLogisticList(Request $request, $id) 
    {
        $select = [
            'logistic_realization_items.id as id',
            'logistic_realization_items.final_product_id as product_id',
            'logistic_realization_items.final_product_name as name',
            'logistic_realization_items.final_quantity as qty',
            'logistic_realization_items.final_unit as unit',
            'logistic_realization_items.final_status as status',
            DB::raw('0 as qty_ok'),
            DB::raw('0 as qty_nok')
        ];
        $data = LogisticRealizationItems::select($select)
        ->where('agency_id', $id)
        ->whereIn('logistic_realization_items.final_status', [LogisticRealizationItems::STATUS_REPLACED, LogisticRealizationItems::STATUS_APPROVED])
        ->get();
        $response = response()->format(200, 'success', $data);
        return $response;
    }
}
