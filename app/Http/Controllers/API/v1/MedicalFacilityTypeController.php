<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\MedicalFacilityType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MedicalFacilityTypeController extends Controller
{
    public function index(Request $request)
    {
        $data = MedicalFacilityType::all(['id', 'name']);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }
}
