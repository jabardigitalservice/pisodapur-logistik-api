<?php

namespace App\Http\Controllers\API\v1\Vaccine;

use App\Http\Controllers\Controller;
use App\Http\Requests\VaccineRequest\GetVaccineProductRequest;
use App\Http\Requests\VaccineRequest\UpdateVaccineProductRequest;
use App\Http\Resources\Vaccine\VaccineProductFinalizationResource;
use App\Http\Resources\Vaccine\VaccineProductRecommendationResource;
use App\Http\Resources\Vaccine\VaccineProductRequestResource;
use App\VaccineProductRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VaccineProductRequestController extends Controller
{
    public function index(GetVaccineProductRequest $request)
    {
        $limit = $request->input('limit', 3);
        $data = VaccineProductRequest::where('vaccine_request_id', $request->input('vaccine_request_id'))
            ->when($request->input('category'), function ($query) use ($request) {
                $query->where('category', $request->input('category'));
            });
        $resource = $data;
        $status = $request->input('status', 'request');
        if ($status == 'request') {
            $resource = VaccineProductRequestResource::collection($data->paginate($limit));
        } elseif ($status == 'recommendation') {
            $resource = VaccineProductRecommendationResource::collection($data->paginate($limit));
        } elseif ($status == 'finalization') {
            $resource = VaccineProductFinalizationResource::collection($data->paginate($limit));
        }
        return $resource;
    }

    public function show(VaccineProductRequest $vaccineProductRequest, Request $request)
    {
        $data['request'] = new VaccineProductRequestResource($vaccineProductRequest);
        $data['recommendation'] = new VaccineProductRecommendationResource($vaccineProductRequest);
        $data['finalization'] = new VaccineProductFinalizationResource($vaccineProductRequest);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function update(VaccineProductRequest $vaccineProductRequest, UpdateVaccineProductRequest $request)
    {
        $vaccineProductRequest->fill($request->validated());
        $vaccineProductRequest->save();
        return response()->format(Response::HTTP_OK, 'Vaccine Product Request Updated');
    }
}
