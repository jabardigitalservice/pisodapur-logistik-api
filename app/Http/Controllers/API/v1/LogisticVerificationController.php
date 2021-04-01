<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Validation;
use App\AcceptanceReport;
use App\LogisticVerification;
use App\Applicant;
use Illuminate\Support\Facades\Mail;
use App\Mail\TokenEmailNotification;
use Carbon\Carbon;

class LogisticVerificationController extends Controller
{
    public function verificationRegistration(Request $request)
    {
        $param['register_id'] = 'required|numeric';
        $message = 'success';
        $response = Validation::validate($request, $param);
        $logisticVerification = [];
        abort_if($response->getStatusCode() != Response::HTTP_OK, $response);
        $findReport = AcceptanceReport::where('agency_id', $request->register_id)->first();
        abort_if($findReport, Response::HTTP_UNPROCESSABLE_ENTITY, 'Permohonan dengan kode ' . $findReport->agency_id . ' sudah dilaporkan pada tanggal ' . Carbon::parse($findReport->date)->format('d-m-Y') . ' oleh ' . $findReport->fullname . '. Terima kasih sudah melaporkan penerimaan barang');
        $request->id = $request->register_id;
        try {
            $applicant = Applicant::where('agency_id', $request->id)->firstOrFail();
            $logisticVerification = LogisticVerification::firstOrCreate(['agency_id' => $request->id], ['email' => $applicant->email]);
            if ($request->has('reset')) {
                $message = 'Kode Verifikasi yang baru telah dikirim ulang ke email Anda.';
                $logisticVerification->expired_at = date('Y-m-d H:i:s', strtotime('-1 days'));
            }
            $response = response()->format(Response::HTTP_OK, $message, $logisticVerification);
        } catch (\Exception $exception) {
            $response = response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, 'Permohonan dengan Kode Permohonan ' . $request->id . ' tidak ditemukan.');
        }
        $logisticVerification = $this->sendEmailCondition($logisticVerification, $response);
        return $response;
    }

    public function sendEmailCondition($logisticVerification, $response)
    {
        if ($response->getStatusCode() === Response::HTTP_OK) {
            if ($logisticVerification->expired_at <= date('Y-m-d H:i:s')) {
                // reset token
                $token = rand(10000, 99999);
                $logisticVerification->token = $token;
                $logisticVerification->expired_at = date('Y-m-d H:i:s', strtotime('+1 days'));
                $logisticVerification->save();
                // send email
                Mail::to($logisticVerification->email)->send(new TokenEmailNotification($token));
            }
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
        if ($response->getStatusCode() === Response::HTTP_OK) {
            // Confirm Token
            $token = $request->verification_code1 . $request->verification_code2 . $request->verification_code3 . $request->verification_code4 . $request->verification_code5;
            $response = response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, 'kode verifikasi tidak sesuai');
            $logisticVerification = LogisticVerification::where('agency_id', $request->register_id)->firstOrfail();
            if ($token == $logisticVerification->token) {
                $response = response()->format(Response::HTTP_OK, 'success', ['token' => $token]);
            }
        }
        return $response;
    }
}
