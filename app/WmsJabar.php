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

    static function sendPing()
    {
        try {
            // Send Notification to WMS Jabar Poslog
            $config['param'] = [];
            $config['apiFunction'] = '/api/pingme';
            $res = self::callAPI($config);

            $outboundPlans = json_decode($res->getBody(), true);
            $response = response()->format(Response::HTTP_OK, 'success', $outboundPlans);
            if ($outboundPlans['msg']) {
                $response = self::insertData($outboundPlans);
            }
        } catch (\Exception $exception) {
            $response = response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage(), $exception->getTrace());
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
            $response = response()->format(Response::HTTP_OK, 'success', $outboundPlans);
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, 'Error Insert Outbound', $exception->getTrace());
        }

        return $response;
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

            $outboundPlans = json_decode($res->getBody(), true);
            return self::updateOutbound($outboundPlans);
        } catch (\Exception $exception) {
            return response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage(), $exception->getTrace());
        }
    }

    static function updateOutbound($outboundPlans)
    {
        DB::beginTransaction();
        try {
            foreach ($outboundPlans['msg'] as $key => $outboundPlan) {
                if (isset($outboundPlan['lo_detil'])) {
                    $lo = $outboundPlan;
                    $lo_detil = $lo['lo_detil'];
                    unset($lo['lo_detil']);
                    Outbound::where('lo_id', $lo['lo_id'])
                            ->update($lo);

                    foreach ($lo_detil as $detil) {
                        OutboundDetail::where('lo_id', $detil['lo_id'])
                                      ->where('material_id', $detil['material_id'])
                                      ->update($detil);
                    }
                }
            }
            DB::commit();
            $response = response()->format(Response::HTTP_OK, 'success', $outboundPlans);
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, 'Error Update Outbound', $exception->getTrace());
        }
        return $response;
    }
}
