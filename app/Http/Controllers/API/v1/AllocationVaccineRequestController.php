<?php

namespace App\Http\Controllers\API\v1;

use App\AllocationRequest;
use App\Enums\AllocationRequestStatusEnum;
use App\Enums\AllocationRequestTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class AllocationVaccineRequestController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $data = AllocationRequest::where('type', AllocationRequestTypeEnum::vaccine())
            ->withCount(['allocationDistributionRequests'])
            ->paginate($limit);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function show(Request $request, $id)
    {
        $data = AllocationRequest::where('type', AllocationRequestTypeEnum::vaccine())
            ->with(['allocationDistributionRequests', 'allocationMaterialRequests'])
            ->withCount(['allocationDistributionRequests'])
            ->find($id);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function statistic(Request $request)
    {
        return [
            'total_requests' => AllocationRequest::where('type', AllocationRequestTypeEnum::vaccine())->count(),
            'total_draft' => AllocationRequest::where('type', AllocationRequestTypeEnum::vaccine())->where('status', AllocationRequestStatusEnum::draft())->count(),
            'total_success' => AllocationRequest::where('type', AllocationRequestTypeEnum::vaccine())->where('status', AllocationRequestStatusEnum::success())->count(),
        ];
    }
}
