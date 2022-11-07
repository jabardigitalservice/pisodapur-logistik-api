<?php

/**
 * Class for storing all method & data regarding item usage information, which
 * are retrieved from Pelaporan API
 */

namespace App;

use App\Enums\AllocationRequestTypeEnum;
use App\Enums\LogisticRequestStatusEnum;
use App\Outbound;
use App\OutboundDetail;
use App\MasterFaskes;
use App\Models\MedicalFacility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;

class WmsJabar extends Usage
{
    static function callAPI($config, $method = 'get')
    {
        try {
            $param = $config['param'];
            $apiLink = $config['url_type'] == 'vaccine' ? config('wmsposlogvaksin.url') : config('wmsjabar.url');
            $apiKey = $config['url_type'] == 'vaccine' ? config('wmsposlogvaksin.key') : config('wmsjabar.key');
            $apiFunction = $config['apiFunction'];
            $url = $apiLink . $apiFunction;

            $attributes = [
                'headers' => [
                    'accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'api-key' => $apiKey,
                ],
                'body' => json_encode($param)
            ];
            if ($method == 'post') {
                return static::getClient()->post($url, $attributes);
            } else {
                return static::getClient()->get($url, $attributes);
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }

    static function insertData($outbounds, $req_type = null)
    {
        $req_type = $req_type ?? AllocationRequestTypeEnum::vaccine();
        DB::beginTransaction();
        $vaccineRequestIds = [];
        try {
            foreach ($outbounds['msg'] as $key => $outbound) {
                if (isset($outbound['lo_detil'])) {
                    $lo = $outbound;
                    $lo['req_type'] = $req_type;
                    Outbound::updateData($lo);
                    OutboundDetail::massInsert($lo['lo_detil'], $req_type);

                    if ($req_type == 'vaccine') {
                        self::updateMedicalFacility($lo);
                    } else {
                        self::updateFaskes($lo);
                    }

                    $vaccineRequestIds[] = $lo['req_id'];
                }
            }
            DB::commit();
            return response()->format(Response::HTTP_OK, 'success', $outbounds);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, 'Error Insert Outbound. Because ' . $exception->getMessage(), $exception->getTrace());
        }
    }

    static function getOutboundById($request)
    {
        try {
            // Send Notification to WMS Jabar Poslog
            $config['param']['req_id'] = $request->input('request_id');
            $config['apiFunction'] = '?route=LO_freqid';
            $config['url_type'] = 'medical';
            $res = self::callAPI($config);

            $outboundPlans = json_decode($res->getBody(), true);
            return self::updateOutbound($outboundPlans);
        } catch (\Exception $exception) {
            return response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage(), $exception->getTrace());
        }
    }

    static function updateOutbound($outboundPlans)
    {
        $update = [];
        DB::beginTransaction();
        try {
            $outbounds = collect($outboundPlans['msg'])->map(function ($outboundPlan) {
                if (isset($outboundPlan['lo_detil'])) {
                    $lo = $outboundPlan;
                    $lo_detil = $lo['lo_detil'];
                    unset($lo['lo_detil']);

                    $update[$lo['lo_id']] = Outbound::where('lo_id', $lo['lo_id'])->update($lo);

                    self::updateFaskes($outboundPlan);

                    $outboundDetail = collect($lo_detil)->map(function ($detil) {
                        OutboundDetail::where('lo_id', $detil['lo_id'])
                            ->where('material_id', $detil['material_id'])
                            ->update($detil);
                    });
                }
            });
            DB::commit();
            $response = response()->format(Response::HTTP_OK, 'success', $outboundPlans);
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, 'Error Update Outbound. Because ' . $exception->getMessage(), $exception->getTrace());
        }
        return $response;
    }

    static function updateAll(Request $request)
    {
        try {
            $map = Outbound::all()->reject(function ($user) {
                return $user->send_to_extid === false;
            })->map(function ($outbound) use ($request) {
                $request->merge(['request_id' => $outbound->req_id]);
                $update[] = self::getOutboundById($request);
            });
            $response = response()->format(Response::HTTP_OK, 'success');
        } catch (\Exception $exception) {
            $response = response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage(), $exception->getTrace());
        }
        return $response;
    }

    static function updateMedicalFacility($outboundPlan)
    {
        return MedicalFacility::where('id', $outboundPlan['send_to_extid'])->update([
            'poslog_id' => $outboundPlan['send_to_id'],
            'poslog_name' => $outboundPlan['send_to_name']
        ]);
    }

    static function updateFaskes($outboundPlan)
    {
        return MasterFaskes::where('id', $outboundPlan['send_to_extid'])->update([
            'poslog_id' => $outboundPlan['send_to_id'],
            'poslog_name' => $outboundPlan['send_to_name'],
            'alamat' => $outboundPlan['send_to_address'],
            'kode_kab_kemendagri' => $outboundPlan['city_id'],
            'nama_kab' => $outboundPlan['send_to_city'],
        ]);
    }

    static function sendRequest($logisticRequest)
    {
        try {
            $config = WmsJabar::setStoreLogisticRequest($logisticRequest);
            $items = $config['param']['data']['finalization_items'];
            // Pre-Validating before Integrating to WMS Poslog
            $dataToIntegrate = WmsJabar::validatingLogisticDataToIntegrate($items);

            if (!($dataToIntegrate['status'] == Response::HTTP_OK)) {
                return response()->format($dataToIntegrate['status'], $dataToIntegrate['message'], $dataToIntegrate['data']);
            }

            // Integrating to WMS Poslog
            $config['apiFunction'] = '?route=pingme_v2';
            $config['url_type'] = 'medical';
            $res = WmsJabar::callAPI($config, 'post');
            $data = json_decode($res->getBody(), true);

            // Handling Validation by WMS Poslog
            if (!isset($data['result'])) {
                return response()->format($data['stt'], 'Failed at WMS Poslog: ' . $data['msg'], [
                    'poslog_error' => $data,
                    'config_data' => $config,
                    'is_valid_to_integrate' => $dataToIntegrate
                ]);
            }
            self::updateApplicant($logisticRequest);
            $lo = $data['result'];
            return WmsJabar::insertData($lo, AllocationRequestTypeEnum::alkes());
        } catch (\Throwable $th) {
            return response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage(), ['config' => $config]);
        }
    }

    static function setStoreLogisticRequest($request)
    {
        $logisticRequest = Applicant::select()
            ->select(
                'agency_id',
                'applicants.id as applicant_id',
                'agency.master_faskes_id',
                'master_faskes.poslog_id',
                'agency.agency_name',
                'agency.location_district_code',
                'agency.location_address',
                'applicant_name',
                'primary_phone_number',
                'application_letter_number',
            )
            ->join('agency', 'agency.id', '=', 'applicants.agency_id')
            ->join('master_faskes', 'master_faskes.id', '=', 'agency.master_faskes_id')
            ->where([
                'agency_id' => $request->agency_id,
                'applicants.id' => $request->applicant_id,
            ])
            ->active()
            ->first();
        $logisticProductRequests = WmsJabar::setFinalLogisticProductRequests($request);

        $finalDate = $logisticProductRequests ? $logisticProductRequests[0]['final_date'] : $logisticRequest->finalized_at;
        $config['param']['data'] = [
            'id' => $logisticRequest->agency_id,
            'master_faskes_id' => $logisticRequest->master_faskes_id,
            'agency_name' => $logisticRequest->agency_name,
            'location_district_code' => $logisticRequest->location_district_code,
            'location_address' => $logisticRequest->location_address,
            'master_faskes' => [
                'poslog_id' => $logisticRequest->poslog_id ?? null
            ],
            'applicant' => [
                'id' => $logisticRequest->agency_id,
                'applicant_name' => $logisticRequest->applicant_name,
                'primary_phone_number' => $logisticRequest->primary_phone_number,
                'application_letter_number' => $logisticRequest->application_letter_number
            ],
            'finalization_items' => $logisticProductRequests,
            'lo_date' => $finalDate
        ];
        return $config;
    }

    static function setFinalLogisticProductRequests($request)
    {
        // 1. final item from user Request
        $userItems = LogisticRealizationItems::select('id', 'final_product_id', 'final_product_name', 'final_quantity', 'final_soh_location', 'final_date')
            ->where([
                'agency_id' => $request->agency_id,
                'applicant_id' => $request->applicant_id,
            ])
            ->where('final_quantity', '>', 0)
            ->whereNull('created_by')
            ->whereNotNull('need_id')
            ->whereNotNull('final_by')
            ->get()->toArray();

        // 2. final item from Admin
        $adminItems = LogisticRealizationItems::select('id', 'final_product_id', 'final_product_name', 'final_quantity', 'final_soh_location', 'final_date')
            ->where([
                'agency_id' => $request->agency_id,
                'applicant_id' => $request->applicant_id,
            ])
            ->where('final_quantity', '>', 0)
            ->whereNotNull('created_by')
            ->whereNotNull('final_by')
            ->get()->toArray();

        $items = $userItems + $adminItems;
        return $items;
    }

    static function validatingLogisticDataToIntegrate($items)
    {
        $result['status'] = Response::HTTP_OK;
        $result['message'] = 'valid';
        $result['data']['item'] = $items;

        // Validate if $items is empty
        if (!(count($items) > 0)) {
            $result['status'] = Response::HTTP_INTERNAL_SERVER_ERROR;
            $result['message'] = 'Maaf, tidak ada barang yang dapat dilanjutkan ke WMS Poslog di permohonan ini. Mohon dicek kembali kuantitas di tiap barangnya.';
            $result['data']['item'] = $items;
        }

        // Validate Stock per item to API SOH
        $isStokValid = WmsJabar::isValidLogisticStock($items);
        if (!$isStokValid['is_valid']) {
            $result['status'] = Response::HTTP_INTERNAL_SERVER_ERROR;
            $result['message'] = $isStokValid['message'];
            $result['data']['item'] = $isStokValid['items'];
            $result['data']['stock_info'] = 'stock not valid';
        }

        return $result;
    }

    static function isValidLogisticStock($items)
    {
        $result['is_valid'] = true;
        $result['items'] = $items;
        $result['message'] = '';

        try {
            foreach ($items as $key => $item) {
                // Get Current Stock from WMS Poslog
                $config['apiFunction'] = '?route=soh_fmaterial';
                $config['url_type'] = 'medical';
                $config['param']['material_id'] = $item['final_product_id'];
                $config['method'] = $item['final_product_id'];
                $res = WmsJabar::callAPI($config);

                $response = json_decode($res->getBody(), true);
                // If Status (stt) Fail/Error.
                if ($response['stt'] == 0) {
                    $result['message'] .= '[' . $item['final_product_id'] . '] ' . $item['final_product_name'] . ' ' . $response['msg'] . '. ';
                    $result['is_valid'] = false;
                    continue;
                }

                $result['items'][$key]['final_soh_location'] = $response['msg'][0]['soh_location'];
                $result['items'][$key]['final_soh_location_name'] = $response['msg'][0]['soh_location_name'];
                $result['items'][$key]['warehouse'] = $response['msg'][0];
                $result = WmsJabar::setValidatingLogisticStockResult($result, $response, $item);
            }
        } catch (\Throwable $th) {
            $result['message'] = $th->getMessage();
        }

        return $result;
    }

    static function setValidatingLogisticStockResult($result, $response, $item)
    {
        // Validating Stock if stock below from request
        $stock = $response['msg'][0]['stock_ok'] - $response['msg'][0]['stock_nok'] - $response['msg'][0]['booked_stock'];
        if ($stock < $item['final_quantity']) {
            $result['message'] .= '[' . $item['final_product_id'] . '] ' . $item['final_product_name'] . ' stok kurang/tidak ada. (' . $stock . '<' . $item['final_quantity'] . ')';
            $result['is_valid'] = false;
        }

        return $result;
    }

    static function updateApplicant($request)
    {
        $dataUpdateApplicant = [
            'is_integrated' => 1,
            'status' => LogisticRequestStatusEnum::integrated(),
            'finalized_by' => auth()->user()->id,
            'integrated_by' => auth()->user()->id,
            'integrated_at' => Carbon::now(),
            'finalized_at' => Carbon::now(),
        ];
        Applicant::updateApplicant($request, $dataUpdateApplicant);
    }
}
