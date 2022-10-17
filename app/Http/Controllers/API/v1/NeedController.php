<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LogisticRequestNeedResource;
use App\Needs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NeedController extends Controller
{
    public function index(Request $request)
    {
        $data = Needs::joinLogisticRealizationItem()
            ->joinProduct('needs', 'product_id')
            ->whereNull('logistic_realization_items.deleted_at')
            ->where('logistic_realization_items.agency_id', $request->agency_id);

        $response = new LogisticRequestNeedResource($data, $request);
        return response()->format(Response::HTTP_OK, 'success', $response);
    }
}
