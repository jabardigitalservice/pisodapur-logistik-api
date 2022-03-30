<?php

namespace App\Http\Controllers\API\v1\Vaccine;

use App\Http\Controllers\Controller;
use App\Http\Requests\VaccineRequest\GetVaccineRequest;
use App\Http\Resources\Vaccine\DeliveryPlanResource;
use App\Models\Vaccine\DeliveryPlan;

class DeliveryPlanController extends Controller
{
    public function index(GetVaccineRequest $request)
    {
        $limit = $request->input('limit', 5);
        $data = DeliveryPlan::deliveryPlan($request)
            ->filter($request)
            ->orderBy('delivery_plan_date', 'desc')
            ->sort($request);
        return DeliveryPlanResource::collection($data->paginate($limit));
    }
}
