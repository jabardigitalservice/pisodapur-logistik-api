<?php

namespace App\Http\Controllers\API\v1;

use App\Outbound;
use App\PoslogProduct;
use App\Agency;
use App\Applicant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\OutboundDetail;
use Carbon\Carbon;
use GuzzleHttp;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Constraint\IsTrue;

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

    static $client = null;

    static function getClient()
    {
        if (static::$client == null) {
            static::$client = new GuzzleHttp\Client();
        }

        return static::$client;
    }

    public function sendPing()
    {
        // Send Notification to WMS Jabar Poslog
        $apiLink = config('wmsjabar.url');
        $apiKey = config('wmsjabar.key');
        $apiFunction = '/api/pingme';
        $url = $apiLink . $apiFunction;
        $res = static::getClient()->get($url, [
            'headers' => [
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $apiKey,
            ]
        ]);

        $response = [ response()->format($res->getStatusCode(), 'Error: WMS Jabar API returning status code ' . $res->getStatusCode()), null ];
        // Store Data Outbounds
        if ($res->getStatusCode() == 200) {
            $outboundPlans = json_decode($res->getBody(), true);
            $response = $this->insertData($outboundPlans);
        }

        return $response;
    }

    private function insertData($outboundPlans)
    {
        DB::beginTransaction();
        $agency_ids = [];
        try {
            foreach ($outboundPlans['msg'] as $key => $outboundPlan) {
                if (isset($outboundPlan['lo_detil'])) {
                    Outbound::create($outboundPlan);
                    OutboundDetail::massInsert($outboundPlan['lo_detil']);

                    $agency_ids[] = $outboundPlan['req_id'];
                }
            }
            //Flagging to applicants by agency_id = req_id
            $applicantFlagging = Applicant::whereIn('agency_id', $agency_ids)->update(['is_integrated' => 1]);
            DB::commit();
            $response = response()->format(200, 'success', $outboundPlans);
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(400, $exception->getMessage());
        }

        return $response;
    }
}
