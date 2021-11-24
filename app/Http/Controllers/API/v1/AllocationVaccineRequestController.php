<?php

namespace App\Http\Controllers\API\v1;

use App\AllocationDistributionRequest;
use App\AllocationMaterialRequest;
use App\AllocationRequest;
use App\Enums\AllocationRequestStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\AllocationRequest\StoreAllocationRequest;
use Carbon\Carbon;
use DB;
use Validator;

class AllocationVaccineRequestController extends Controller
{
    public $materialDataRule = [
        'matg_id' => 'required|exists:allocation_materials,matg_id',
        'material_id' => 'required|exists:allocation_materials,material_id',
        'material_name' => 'required',
        'qty' => 'required|numeric',
    ];

    public $distributionDataRule = [
        'agency_id' => 'required|exists:master_faskes,id',
        'agency_name' => 'required',
        'distribution_plan_date' => 'required|date_format:Y-m-d',
        'allocation_material_requests' => 'required'
    ];

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

    public function store(StoreAllocationRequest $request)
    {
        $distributionList = $request->input('instance_list');
        $errors = $this->getErrorValidate($distributionList);

        if ($errors) {
            return response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
        }

        return $this->storeData($request, $distributionList);
    }

    public function getErrorValidate($distributionList)
    {
        $errors = [];
        $distributionList = !is_array($distributionList) ? json_decode($distributionList) : $distributionList;
        foreach ($distributionList as $list) {
            $validator = Validator::make((array) $list, $this->distributionDataRule);
            if ($validator->fails()) {
                $errors['allocation_request'] = $validator->errors()->messages();
            }

            foreach ($list->allocation_material_requests as $materialList) {
                $validator = Validator::make((array) $materialList, $this->materialDataRule);
                if ($validator->fails()) {
                    $errors['allocation_request'] = $validator->errors()->messages();
                }
            }
        }

        return $errors;
    }

    public function storeData($request, $distributionList)
    {
        DB::beginTransaction();
        try {
            $allocationRequest = AllocationRequest::create($request->validated());

            $materialRequests = [];

            $distributionList = !is_array($distributionList) ? json_decode($distributionList) : $distributionList;
            foreach ($distributionList as $list) {
                $allocationDistribution = (array) $list;
                $allocationDistribution['allocation_request_id'] = $allocationRequest->id;
                $allocationDistributionRequest = AllocationDistributionRequest::create($allocationDistribution);

                $distributionID = $allocationDistributionRequest->id;
                foreach ($list['allocation_material_requests'] as $key => $materialList) {
                    $material = (array) $materialList;
                    $material['allocation_request_id'] = $allocationRequest->id;
                    $material['allocation_distribution_request_id'] = $distributionID;
                    $material['created_at'] = Carbon::now();
                    $materialRequests[] = $material;
                }
            }

            AllocationMaterialRequest::insert($materialRequests);

            DB::commit();
            return response()->format(Response::HTTP_OK, 'success');
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception->getTrace());
        }
    }
}
