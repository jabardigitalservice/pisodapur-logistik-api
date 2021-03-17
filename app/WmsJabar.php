<?php

/**
 * Class for storing all method & data regarding item usage information, which
 * are retrieved from Pelaporan API
 */

namespace App;
use App\Outbound;
use App\OutboundDetail;
use DB;

class WmsJabar extends Usage
{
    static function sendPing()
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
            $response = self::insertData($outboundPlans);
        }

        return $response;
    }

    static function insertData($outboundPlans)
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
