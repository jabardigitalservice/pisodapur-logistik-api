<?php

namespace App\Http\Controllers\API\v1;

use App\AllocationRequest;
use App\Enums\AllocationRequestStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use DB;

class AllocationRequestController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $data = AllocationRequest::alkes()->filter($request)
        ->paginate($limit);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function show(Request $request, $id)
    {
        $data = AllocationRequest::alkes()
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
            'total_success' => AllocationRequest::alkes()->where('status', AllocationRequestStatusEnum::success())->count(),
            'total_draft' => AllocationRequest::alkes()->where('status', AllocationRequestStatusEnum::draft())->count(),
            'total_requests' => AllocationRequest::alkes()->count(),
        ];
    }
}
