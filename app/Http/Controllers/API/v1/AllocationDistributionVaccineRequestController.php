<?php

namespace App\Http\Controllers\API\v1;

use App\AllocationDistributionRequest;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\AllocationRequest\GetAllocationDistributionVaccineRequest;

class AllocationDistributionVaccineRequestController extends Controller
{
    public function index(GetAllocationDistributionVaccineRequest $request)
    {
        $limit = $request->input('limit', 10);
        $data = AllocationDistributionRequest::filter($request)
            ->where('allocation_request_id', $request->input('allocation_request_id'))
            ->with(['allocationMaterialRequests'])
            ->paginate($limit);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }
}
