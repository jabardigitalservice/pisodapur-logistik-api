<?php

namespace App\Http\Controllers\API\v1;

use App\Outbound;
use App\PoslogProduct;
use App\Agency;
use App\Applicant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OutboundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = Outbound::readyToDeliver();
        return response()->format(200, 'success', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Outbound  $outbound
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $syncSohLocation = \App\PoslogProduct::syncSohLocation();
        $logisticRequest = Agency::getList($request, false)
        ->join('applicants', 'agency.id', '=', 'applicants.agency_id')
        ->where('is_deleted', '!=' , 1)
        ->where('agency.id', $id)
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Outbound  $outbound
     * @return \Illuminate\Http\Response
     */
    public function notification(Request $request)
    {
        return "success";
    }
}
