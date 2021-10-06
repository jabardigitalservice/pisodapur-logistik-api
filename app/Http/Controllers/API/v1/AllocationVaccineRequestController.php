<?php

namespace App\Http\Controllers\API\v1;

use App\AllocationRequest;
use App\Enums\AllocationRequestStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use DB;

class AllocationVaccineRequestController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $data = AllocationRequest::vaccine()->filter($request)
            ->withCount(['allocationDistributionRequests'])
            ->paginate($limit);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function show(Request $request, $id)
    {
        $data = AllocationRequest::vaccine()
            ->with([
                'allocationDistributionRequests.allocationMaterialRequests',
                'allocationMaterialRequests' => function ($query) {
                    $query->select(['allocation_request_id', 'material_id', 'material_name', DB::raw('sum(qty) as total_qty'), 'UoM'])
                          ->groupByRaw('allocation_request_id, material_id, material_name, UoM');
                }
            ])
            ->withCount(['allocationDistributionRequests'])
            ->find($id);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function statistic(Request $request)
    {
        return [
            'total_success' => AllocationRequest::vaccine()->where('status', AllocationRequestStatusEnum::success())->count(),
            'total_draft' => AllocationRequest::vaccine()->where('status', AllocationRequestStatusEnum::draft())->count(),
            'total_requests' => AllocationRequest::vaccine()->count(),
        ];
    }
}
