<?php

/**
 * Class for storing all method & data regarding item usage information, which
 * are retrieved from Pelaporan API
 */

namespace App;

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
            return response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage(), $exception->getTrace());
        }
    }

    static function getVaccineByIDMaterial($request, $id)
    {
        $request->input('material_id', $id);
        $param = $request->all();
        $param['material_id'] = $id;
        try {
            $config['param'] = $param;
            $config['apiFunction'] = '/api_vaksin/index.php?route=soh_fmaterial';
            $res = self::callAPI($config);

            $data = json_decode($res->getBody(), true);
            self::storeAllocationMaterialVaccine($data['msg']);
        } catch (\Exception $exception) {
            return response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage(), $exception->getTrace());
        }
    }

    static function storeAllocationMaterialVaccine($materials)
    {
        foreach ($materials as $material) {
            $material['type'] = 'vaccine';
            $material['created_at'] = date('Y-m-d H:i:s');
            $material['updated_at'] = date('Y-m-d H:i:s');
            $material['type'] = 'vaccine';
            $store = AllocationMaterial::updateOrInsert(
                ['material_id' => $material['material_id']],
                $material
            );
        }
    }
}
