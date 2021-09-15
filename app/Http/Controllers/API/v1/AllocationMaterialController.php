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
        $type = $request->input('type', 'alkes');
        $limit = $request->input('limit', 10);
        $data = AllocationMaterial::where('type', $type)->paginate($limit);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function show(Request $request, $id)
    {
        $type = $request->input('type', 'alkes');
        $data = AllocationMaterial::where('type', $type)->find($id);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }
}
