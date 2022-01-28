<?php

/**
 * Class for storing all method & data regarding item usage information, which
 * are retrieved from Pelaporan API
 */

namespace App;

use App\Enums\AllocationRequestTypeEnum;
use Illuminate\Http\Response;
use DB;

class VaccineWmsJabar extends WmsJabar
{
    static function getAllVaccineMaterial()
    {
        try {
            $config['param']['soh_location'] = 'WHS_VAKSIN';
            $config['apiFunction'] = '/api_vaksin/index.php?route=soh_flocation';
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

    static function setStoreRequest(VaccineRequest $vaccineRequest)
    {
        $finalization_items = [];
        foreach ($vaccineRequest->vaccineProductRequests as $product) {
            $soh_location = AllocationMaterial::select('soh_location')->where('material_id', $product->finalized_product_id)->first();
            $finalization_items[] = [
                'id' => $product->id,
                'final_product_id' => $product->finalized_product_id,
                'final_quantity' => $product->finalized_quantity,
                'final_soh_location' => $soh_location->soh_location,
            ];
        }

        $config['param']['data'] = [
            'id' => $vaccineRequest->id,
            'master_faskes_id' => $vaccineRequest->agency_id,
            'agency_name' => $vaccineRequest->masterFaskes->nama_faskes,
            'location_district_code' => $vaccineRequest->agency_city_id,
            'location_address' => $vaccineRequest->agency_address,
            'master_faskes' => [
                'poslog_id' => $vaccineRequest->masterFaskes->poslog_id
            ],
            'applicant' => [
                'id' => $vaccineRequest->id,
                'applicant_name' => $vaccineRequest->applicant_fullname,
                'primary_phone_number' => $vaccineRequest->applicant_primary_phone_number,
                'application_letter_number' => $vaccineRequest->letter_number
            ],
            'finalization_items' => $finalization_items
        ];

        return $config;
    }

    static function sendVaccineRequest(VaccineRequest $vaccineRequest)
    {
        try {
            $config = self::setStoreRequest($vaccineRequest);
            $config['apiFunction'] = '/api_vaksin/index.php?route=pingme_v2';
            $res = self::callAPI($config, 'post');

            $data = json_decode($res->getBody(), true);

            if ($data['result']['stt'] != 1) {
                return response()->format($data['result']['stt'], 'Failed at WMS Poslog: ' . $data['msg'], $data['error']);
            }

            $lo = $data['result'];
            return self::insertDataLOVaccine($lo);
        } catch (\Exception $exception) {
            return response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception->getTrace());
        }
    }

    static function insertDataLOVaccine($outboundPlans)
    {
        DB::beginTransaction();
        $vaccineRequestIds = [];
        try {
            foreach ($outboundPlans['msg'] as $key => $outboundPlan) {
                if (isset($outboundPlan['lo_detil'])) {
                    OutboundVaccine::updateOrCreate([
                            'lo_id' => $outboundPlan['lo_id'],
                            'req_id' => $outboundPlan['req_id']
                        ],
                        $outboundPlan
                    );
                    OutboundDetailVaccine::massInsert($outboundPlan['lo_detil']);
                    self::updateFaskes($outboundPlan);

                    $vaccineRequestIds[] = $outboundPlan['req_id'];
                }
            }
            $flagging = VaccineRequest::whereIn('id', $vaccineRequestIds)->update(['is_integrated' => 1]);
            DB::commit();
            $response = response()->format(Response::HTTP_OK, 'success', $outboundPlans);
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, 'Error Insert Outbound. Because ' . $exception->getMessage(), $exception->getTrace());
        }

        return $response;
    }
}
