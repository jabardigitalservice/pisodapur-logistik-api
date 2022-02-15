<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\MedicalFacility;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MedicalFacilityController extends Controller
{
    public function index(Request $request)
    {
        $data = MedicalFacility::when($request->input('name'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            })
            ->when($request->input('medical_facility_type_id'), function ($query) use ($request) {
                $query->where('medical_facility_type_id', $request->input('medical_facility_type_id'));
            })
            ->get();
        return response()->format(Response::HTTP_OK, 'success', $data);
    }
}
