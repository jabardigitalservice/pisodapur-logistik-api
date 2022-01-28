<?php

namespace App\Http\Controllers\API\v1;

use App\AllocationMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\AllocationRequest\GetAllocationMaterialRequest;
use App\PoslogProduct;
use App\SyncApiSchedules;
use App\VaccineWmsJabar;

class AllocationMaterialController extends Controller
{
    public function index(GetAllocationMaterialRequest $request)
    {
        if ($this->checkOutdated()) {
            VaccineWmsJabar::getAllVaccineMaterial();
        }

        $isPaginated = $request->input('is_paginated', 1);
        $type = $request->input('type', 'vaccine');
        $limit = $request->input('limit', 10);

        $data = AllocationMaterial::where('type', $type)
                ->when($request->input('material_name'), function ($query) use ($request) {
                    $query->where('material_name', 'LIKE', "%{$request->input('material_name')}%");
                })->when($request->input('matg_id'), function ($query) use ($request) {
                    $query->where('matg_id', $request->input('matg_id'));
                })
                ->whereRaw('(stock_ok - booked_stock) > 0')
                ->orderBy('material_name');

        $data = $isPaginated ? $data->paginate($limit) : $data->get();
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function show(Request $request, $id)
    {
        if ($this->checkOutdated()) {
            VaccineWmsJabar::getVaccineByIDMaterial($request, $id);
        }

        $type = $param['type'] ?? 'vaccine';
        $data = AllocationMaterial::where('type', $type)->where('material_id', $id)->firstOrFail();
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function checkOutdated()
    {
        $result = false;
        $updateTime = AllocationMaterial::orderBy('created_at', 'desc')->value('created_at');
        $result = $this->isOutdated($updateTime);
        // $result = $updateTime ? $this->isOutdated($updateTime) : true;
        return $result;
    }

    public function isOutdated($updateTime)
    {
        $time = date('Y-m-d H:i:s');
        $baseApi = PoslogProduct::API_POSLOG;
        $updateTime = SyncApiSchedules::getIntervalTimeByAPI($baseApi, $updateTime);
        return $updateTime < $time ?? false;
    }
}
