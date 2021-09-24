<?php

namespace App\Http\Controllers\API\v1;

use App\AllocationRequest;
use App\Enums\AllocationRequestStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class AllocationRequestController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $data = AllocationRequest::alkes()
        ->paginate($limit);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function show(Request $request, $id)
    {
        $data = AllocationRequest::alkes()
        ->with(['allocationDistributionRequests', 'allocationMaterialRequests'])
        ->find($id);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function statistic(Request $request)
    {
        $allocationRequest = AllocationRequest::alkes();
        return [
            'total_requests' => $allocationRequest->count(),
            'total_draft' => $allocationRequest->where('status', AllocationRequestStatusEnum::draft())->count(),
            'total_success' => $allocationRequest->where('status', AllocationRequestStatusEnum::success())->count(),
        ];
    }
}
