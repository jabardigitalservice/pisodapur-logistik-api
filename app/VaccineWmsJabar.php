<?php

/**
 * Class for storing all method & data regarding item usage information, which
 * are retrieved from Pelaporan API
 */

namespace App;

use App\Enums\AllocationRequestTypeEnum;
use App\Models\Vaccine\VaccineRequest;
use Illuminate\Http\Response;
use DB;

class VaccineWmsJabar extends WmsJabar
{
    static function getAllVaccineMaterial()
    {
        try {
            $config['param']['soh_location'] = 'WHS_VAKSIN';
            $config['apiFunction'] = '/api_vaksin/index.php?route=soh_flocation';
            $config['url_type'] = 'vaccine';
            $res = self::callAPI($config);

            $data = json_decode($res->getBody(), true);
            self::storeAllocationMaterialVaccine($data['msg']);

            $config['param']['soh_location'] = 'WHS_PENUNJANG_IF';
            $config['apiFunction'] = '/api_vaksin/index.php?route=soh_flocation';
            $res = self::callAPI($config);

            $data = json_decode($res->getBody(), true);
            self::storeAllocationMaterialVaccine($data['msg']);
        } catch (\Exception $exception) {
            return response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception->getTrace());
        }
    }

    static function getVaccineByIDMaterial($request, $id)
    {
        $param = $request->all();
        $param['material_id'] = $id;
        try {
            $config['param'] = $param;
            $config['apiFunction'] = '/api_vaksin/index.php?route=soh_fmaterial';
            $config['url_type'] = 'vaccine';
            $res = self::callAPI($config);

            $data = json_decode($res->getBody(), true);
            self::storeAllocationMaterialVaccine($data['msg']);
        } catch (\Exception $exception) {
            return response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception->getTrace());
        }
    }

    static function storeAllocationMaterialVaccine($materials)
    {
        foreach ($materials as $material) {
            $material['type'] = AllocationRequestTypeEnum::vaccine();
            $material['created_at'] = date('Y-m-d H:i:s');
            $material['updated_at'] = date('Y-m-d H:i:s');
            $store = AllocationMaterial::updateOrInsert(
                ['material_id' => $material['material_id']],
                $material
            );
        }
    }

    static function setFinalVaccineProductRequests(VaccineRequest $vaccineRequest)
    {
        $vaccineProductRequests = [];
        foreach ($vaccineRequest->vaccineProductRequests as $product) {
            if ($product->finalized_quantity > 0) {
                $soh_location = AllocationMaterial::select('soh_location')->where('material_id', $product->finalized_product_id)->first();
                $vaccineProductRequests[] = [
                    'id' => $product->id,
                    'final_product_id' => $product->finalized_product_id,
                    'final_product_name' => $product->finalized_product_name,
                    'final_quantity' => $product->finalized_quantity,
                    'final_soh_location' => $soh_location->soh_location ?? "",
                ];
            }
        }
        return $vaccineProductRequests;
    }

    static function setStoreRequest(VaccineRequest $vaccineRequest)
    {
        $vaccineProductRequests = self::setFinalVaccineProductRequests($vaccineRequest);

        $config['param']['data'] = [
            'id' => $vaccineRequest->id,
            'master_faskes_id' => $vaccineRequest->agency_id,
            'agency_name' => $vaccineRequest->agency_name,
            'location_district_code' => $vaccineRequest->agency_city_id,
            'location_address' => $vaccineRequest->agency_address,
            'master_faskes' => [
                'poslog_id' => $vaccineRequest->medicalFacility->poslog_id ?? null
            ],
            'applicant' => [
                'id' => $vaccineRequest->id,
                'applicant_name' => $vaccineRequest->applicant_fullname,
                'primary_phone_number' => $vaccineRequest->applicant_primary_phone_number,
                'application_letter_number' => $vaccineRequest->letter_number
            ],
            'finalization_items' => $vaccineProductRequests,
            'lo_date' => $vaccineRequest->delivery_plan_date
        ];

        return $config;
    }

    static function sendVaccineRequest(VaccineRequest $vaccineRequest)
    {
        try {
            $config = self::setStoreRequest($vaccineRequest);
            $items = $config['param']['data']['finalization_items'];

            // Pre-Validating before Integrating to WMS Poslog
            $isValidToIntegrate = self::isValidToIntegrate($items);
            if (!$isValidToIntegrate['status'] == Response::HTTP_INTERNAL_SERVER_ERROR) {
                return response()->format($isValidToIntegrate['status'], $isValidToIntegrate['message'], $isValidToIntegrate['data']);
            }

            // Integrating to WMS Poslog
            $config['apiFunction'] = '/api_vaksin/index.php?route=pingme_v2';
            $config['url_type'] = 'vaccine';
            $res = self::callAPI($config, 'post');

            $data = json_decode($res->getBody(), true);

            // Handling Validation by WMS Poslog
            if (!isset($data['result'])) {
                return response()->format($data['stt'], 'Failed at WMS Poslog: ' . $data['msg'], [
                    'poslog_error' => $data,
                    'config_data' => $config
                ]);
            }

            $lo = $data['result'];
            return self::insertData($lo, AllocationRequestTypeEnum::vaccine());
        } catch (\Exception $exception) {
            return response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception->getTrace());
        }
    }

    static function isValidToIntegrate($items)
    {
        $result = [
            'status' => Response::HTTP_OK,
            'message' => 'valid',
            'data' => [],
        ];

        // Validate if $items is empty
        if (!(count($items) > 0)) {
            $result['status'] = Response::HTTP_INTERNAL_SERVER_ERROR;
            $result['message'] = 'Maaf, tidak ada barang yang dapat dilanjutkan ke WMS Poslog di permohonan ini. Mohon dicek kembali kuantitas di tiap barangnya.';
            $result['data'] = $items;
        }

        // Validate Stock per item to API SOH
        $isStokValid = self::isValidStock($items);
        if (!$isStokValid['is_valid']) {
            $result['status'] = Response::HTTP_INTERNAL_SERVER_ERROR;
            $result['message'] = $isStokValid['message'];
            $result['data'] = $isStokValid['items'];
        }

        return $result;
    }

    static function isValidStock($items)
    {
        $result['is_valid'] = true;
        $result['items'] = $items;
        $result['message'] = '';

        foreach ($items as $key => $item) {
            // Get Current Stock from WMS Poslog
            $config['apiFunction'] = '/api_vaksin/index.php?route=soh_fmaterial';
            $config['url_type'] = 'vaccine';
            $config['param']['material_id'] = $item['final_product_id'];
            $config['method'] = $item['final_product_id'];
            $res = self::callAPI($config, 'post');

            $response = json_decode($res->getBody(), true);

            // If Status (stt) Fail/Error.
            if ($response['stt'] == 0) {
                $result['message'] .= '['. $item['final_product_id'] . '] ' . $item['final_product_name'] . ' ' . $response['msg'] . '. ';
                $result['is_valid'] = false;
                continue;
            }

            $result['items'][$key]['final_soh_location'] = $response['msg'][0]['soh_location'];
            $result['items'][$key]['final_soh_location_name'] = $response['msg'][0]['soh_location_name'];
            $result['items'][$key]['warehouse'] = $response['msg'][0];

            $result = self::setValidatingStockResult($result, $response, $item);
        }

        return $result;
    }

    static function setValidatingStockResult($result, $response, $item)
    {
        // Validating Stock if stock below from request
        $stock = $response['msg'][0]['stock_ok'] - $response['msg'][0]['stock_nok'] - $response['msg'][0]['booked_stock'];
        if ($stock < $item['final_quantity']) {
            $result['message'] .= '['. $item['final_product_id'] . '] ' . $item['final_product_name'] . ' stok kurang/tidak ada. (' . $stock . '<' . $item['final_quantity'] . ')';
            $result['is_valid'] = false;
        }

        return $result;
    }
}
