<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\VaccineRequest\GetVaccineProductRequest;
use App\Http\Requests\VaccineRequest\UpdateVaccineProductRequest;
use App\VaccineProductRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VaccineProductRequestController extends Controller
{
    public function index(GetVaccineProductRequest $request)
    {
        $limit = $request->input('limit', 3);
        $data = VaccineProductRequest::where('vaccine_request_id', $request->input('vaccine_request_id'))
            ->paginate($limit);
        return response()->json($data, Response::HTTP_OK);
    }

    public function update(VaccineProductRequest $vaccineProductRequest, UpdateVaccineProductRequest $request)
    {
        $vaccineProductRequest->fill($request->validated());
        $vaccineProductRequest->save();
        return response()->format(Response::HTTP_OK, 'Vaccine Product Request Updated');
    }
}
