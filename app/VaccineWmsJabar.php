<?php

/**
 * Class for storing all method & data regarding item usage information, which
 * are retrieved from Pelaporan API
 */

namespace App;

use App\Enums\AllocationRequestTypeEnum;
use Carbon\Carbon;
use Illuminate\Http\Response;

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

    static function sendVaccineRequest(VaccineRequest $vaccineRequest)
    {
        try {
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
                'finalization_items' => $vaccineRequest->vaccineProductRequests()
            ];

            // return $config;
            $config['apiFunction'] = '/api_vaksin/index.php?route=pingme_v2';
            $res = self::callAPI($config, 'post');

            $data = json_decode($res->getBody(), true);

            if ($data['stt'] != 200) {
                return response()->format($data['stt'], 'Failed at WMS Poslog: ' . $data['msg'], $data['error']);
            }

            $lo = $data['msg'];
            return self::storeLO($lo);
        } catch (\Exception $exception) {
            return response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception->getTrace());
        }
    }

    static function storeLO($los)
    {
        try {
            $detil = [];
            foreach ($los as $key => $lo) {
                $detil[] = $lo['lo_detil'];
                $lo[$key]['created_at'] = Carbon::now();
                $lo[$key]['updated_at'] = Carbon::now();
            }
            $outbound = Outbound::insert($lo);
            $outboundDetail = OutboundDetail::insert($detil);
            return response()->format(Response::HTTP_CREATED, 'LO created!', $los);
        } catch (\Exception $exception) {
            return response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception->getTrace());
        }
    }
}
