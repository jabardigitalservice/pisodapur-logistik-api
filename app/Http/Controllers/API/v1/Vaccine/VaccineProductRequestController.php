<?php

namespace App\Http\Controllers\API\v1\Vaccine;

use App\Enums\VaccineRequestStatusEnum;
use App\FileUpload;
use App\Http\Controllers\Controller;
use App\Http\Requests\VaccineRequest\GetVaccineProductRequest;
use App\Http\Requests\VaccineRequest\GetVaccineStockRequest;
use App\Http\Requests\VaccineRequest\StoreVaccineProductRequest;
use App\Http\Requests\VaccineRequest\UpdateVaccineProductRequest;
use App\Http\Resources\Vaccine\VaccineProductFinalizationResource;
use App\Http\Resources\Vaccine\VaccineProductDeliveryPlanResource;
use App\Http\Resources\Vaccine\VaccineProductRecommendationResource;
use App\Http\Resources\Vaccine\VaccineProductRequestResource;
use App\Models\Vaccine\VaccineProduct;
use App\VaccineProductRequest;
use App\VaccineWmsJabar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use DB;

class VaccineProductRequestController extends Controller
{
    public function index(GetVaccineProductRequest $request)
    {
        $limit = $request->input('limit', 3);
        $isPaginated = $request->input('is_paginated', 1);

        $data = VaccineProductRequest::where('vaccine_request_id', $request->input('vaccine_request_id'))
            ->when($request->input('category'), function ($query) use ($request) {
                $query->where('category', $request->input('category'));
            });
        $resource = $data;
        $status = $request->input('status', 'request');
        $data = $isPaginated ? $data->paginate($limit) : $data->get();
        if ($status == 'request') {
            $resource = VaccineProductRequestResource::collection($data);
        } elseif ($status == 'recommendation') {
            $resource = VaccineProductRecommendationResource::collection($data);
        } elseif ($status == 'finalization') {
            $resource = VaccineProductFinalizationResource::collection($data);
        } elseif ($status == 'delivery_plan') {
            $resource = VaccineProductDeliveryPlanResource::collection($data);
        }
        return $resource;
    }

    public function show(VaccineProductRequest $vaccineProductRequest, Request $request)
    {
        $data['request'] = new VaccineProductRequestResource($vaccineProductRequest);
        $data['recommendation'] = new VaccineProductRecommendationResource($vaccineProductRequest);
        $data['finalization'] = new VaccineProductFinalizationResource($vaccineProductRequest);
        $data['delivery_plan'] = new VaccineProductDeliveryPlanResource($vaccineProductRequest);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function store(StoreVaccineProductRequest $request)
    {
        $request->merge(['recommendation_file_url' => Storage::put(FileUpload::LETTER_PATH, $request->file('recommendation_file'))]);
        $vaccineProductRequest = new VaccineProductRequest();
        $vaccineProductRequest->fill($request->validated());
        $vaccineProductRequest->recommendation_file_url = $request->recommendation_file_url;
        $vaccineProductRequest->recommendation_by = auth()->user()->id;
        $vaccineProductRequest->recommendation_date = Carbon::now();
        $vaccineProductRequest->save();
        return response()->format(Response::HTTP_CREATED, 'Vaccine Product Request Created');
    }

    public function update(VaccineProductRequest $vaccineProductRequest, UpdateVaccineProductRequest $request)
    {
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'Error';

        DB::beginTransaction();
        try {
            $vaccineProductRequest->fill($request->validated());
            $vaccineProductRequest->save();

            $status = Response::HTTP_OK;
            $message = 'Vaccine Product Request Updated';
            $data = $vaccineProductRequest;

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $message = $th->getMessage();
            $data = $th->getTrace();
        }
        return response()->format($status, $message, $data);
    }

    public function checkStock(Request $request)
    {
        $data = VaccineProductRequest::select(
            'finalized_product_id as final_product_id'
            , 'finalized_product_name as final_product_name'
            , 'finalized_quantity as final_quantity'
        )
            ->where('vaccine_request_id', $request->vaccine_request_id)
            ->where('finalized_quantity', '>', 0)
            ->whereNotNull('finalized_product_id')
            ->get()
            ->toArray();

        $result = VaccineWmsJabar::isValidStock($data);

        $status = $result['is_valid'] ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR;
        return response()->json([
            'status' => $status,
            'message' => $result['message'],
            'data' => $result,
            'request' => $request->all(),
        ], $status);
    }

    public function checkStockByMaterialId($id, Request $request)
    {
        $data = [
            'warehouse' => 0,
            'verified' => 0,
            'approved' => 0,
            'finalized' => 0,
            'current_stock' => 0,
        ];

        $param[] = [
            'final_product_id' => $id,
            'final_product_name' => '',
            'final_quantity' => 0,
        ];

        $result = $this->setDataStock($id, $param, $data);
        return response()->json($result, $result['status']);
    }

    function setDataStock($id, $param, $data)
    {
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'Error.';
        $validStock = [];
        try {
            $validStock = VaccineWmsJabar::isValidStock($param);
            $message .= $validStock['message'];

            if ($validStock['is_valid'] && count($validStock['items']) > 0) {
                $status = Response::HTTP_OK;
                $message = 'success';
                $data['warehouse'] = $validStock['items'][0]['warehouse']['stock_ok'] - $validStock['items'][0]['warehouse']['stock_nok'] - $validStock['items'][0]['warehouse']['booked_stock'];
                $data['verified'] = $this->getPhaseStockRequest($id, [VaccineRequestStatusEnum::verified(), VaccineRequestStatusEnum::verified_with_note()]);
                $data['approved'] = $this->getPhaseStockRequest($id, [VaccineRequestStatusEnum::approved()]);
                $data['finalized'] = $this->getPhaseStockRequest($id, [VaccineRequestStatusEnum::finalized()]);
                $data['current_stock'] = $data['warehouse'] - ($data['approved'] + $data['finalized']);
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
        }

        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'result' => $validStock
        ];
    }

    function getPhaseStockRequest($id, $status)
    {
        $recommendation = VaccineProductRequest::query()
            ->join('vaccine_requests as vr', 'vr.id', '=', 'vaccine_request_id')
            ->whereIn('vr.status', $status)
            ->where([
                'recommendation_product_id' => $id,
                'finalized_status' => null,
            ])
            ->sum('recommendation_quantity');

        $finalized = VaccineProductRequest::query()
            ->join('vaccine_requests as vr', 'vr.id', '=', 'vaccine_request_id')
            ->whereIn('vr.status', $status)
            ->where('finalized_product_id', $id)
            ->sum('finalized_quantity');

        $result = $recommendation + $finalized;
        return $result;
    }
}
