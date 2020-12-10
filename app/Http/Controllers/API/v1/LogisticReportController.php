<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Validation;
use App\LogisticVerification;
use App\Applicant;
use Illuminate\Support\Facades\Mail;
use App\Mail\TokenEmailNotification;

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
                $response = response()->format(422, 'Permohonan dengan Kode Permohonan ' . $request->id . ' tidak ditemukan.', $exception->getMessage());
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
}
