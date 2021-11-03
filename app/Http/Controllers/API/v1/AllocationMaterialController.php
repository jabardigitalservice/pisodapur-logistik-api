<?php

namespace App\Http\Controllers\API\v1;

use App\AllocationMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class AllocationMaterialController extends Controller
{
    public function index(Request $request)
    {
        $isPaginated = $request->input('is_paginated', 0);
        $type = $request->input('type', 'vaccine');
        $limit = $request->input('limit', 10);
        $data = AllocationMaterial::where('type', $type)
        $data = $isPaginated ? $data->get() : $data->paginate($limit);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function show(Request $request, $id)
    {
        $type = $request->input('type', 'vaccine');
        $data = AllocationMaterial::where('type', $type)->where('material_id', $id)->firstOrFail();
        return response()->format(Response::HTTP_OK, 'success', $data);
    }
}
