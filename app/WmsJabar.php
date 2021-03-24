<?php

/**
 * Class for storing all method & data regarding item usage information, which
 * are retrieved from Pelaporan API
 */

namespace App;
use App\Outbound;
use App\OutboundDetail;
use Illuminate\Http\Response;
use DB;

class WmsJabar extends Usage
{
    static function callAPI($config)
    {
        try {
            $param = $config['param'];
            $apiLink = config('wmsjabar.url');
            $apiKey = config('wmsjabar.key');
            $apiFunction = $config['apiFunction'];
            $url = $apiLink . $apiFunction;
            return static::getClient()->get($url, [
                'headers' => [
                    'accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'api-key' => $apiKey,
                ],
                'body' => json_encode($param)
            ]);
        } catch (\Throwable $th) {
            return $th;
        }
    }

    static function getOutboundById($request)
    {
        try {
            // Send Notification to WMS Jabar Poslog
            $config['param'] = [
                'request_id' => $request->input('request_id')
            ];
            $config['apiFunction'] = '/api/outbound_fReqID';
            $res = self::callAPI($config);

            return json_decode($res->getBody(), true);
        } catch (\Throwable $th) {
            return response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, 'Error Access: WMS Jabar API');
        }
    }

    static function sendPing()
    {
        try {
            // Send Notification to WMS Jabar Poslog
            $config['param'] = '';
            $config['apiFunction'] = '/api/pingme';
            $res = self::callAPI($config);

            $outboundPlans = json_decode($res->getBody(), true);
            return self::insertData($outboundPlans);
        } catch (\Throwable $th) {
            return response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, 'Error Access: WMS Jabar API');
        }
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
            $response = response()->format(Response::HTTP_OK, 'success', $outboundPlans);
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage());
        }

        return $response;
    }
}
