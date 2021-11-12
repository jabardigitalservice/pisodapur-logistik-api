<?php

namespace App\Http\Controllers\API\v1;

use App\AllocationDistributionRequest;
use App\AllocationMaterial;
use App\AllocationMaterialRequest;
use App\AllocationRequest;
use App\Enums\AllocationRequestStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportExcelRequest;
use App\Imports\MultipleSheetImport;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Validator;

class ImportAllocationVaccineRequestController extends Controller
{
    public $allocationRequestRule = [
        'letter_number' => 'required|unique:allocation_requests,letter_number',
        'letter_date' => 'required|date',
        'applicant_name' => 'required',
        'applicant_position' => 'required',
        'applicant_agency_id' => 'required|exists:master_faskes,id',
        'applicant_agency_name' => 'required',
        'letter_url' => 'required'
    ];

    public $allocationDistributionRule = [
        'agency_id' => 'required|exists:master_faskes,id',
        'agency_name' => 'required',
        'distribution_plan_date' => 'required|date',
    ];

    public $allocationMaterialRequestRule = [
        'matg_id' => 'required|exists:allocation_materials,matg_id',
        'material_id' => 'required|exists:allocation_materials,material_id',
        'material_name' => 'required'
    ];

    public $allocationRequest;
    public $arrAllocationDistribution;

    public function import(ImportExcelRequest $request)
    {
        $errors = [];
        $import = new MultipleSheetImport();
        Excel::import($import, request()->file('file'));
        $allocations = $import->sheetData[2]->toArray();

        // 1. Validasi
        $errors = $this->validateAllocationRequest($allocations, $errors);
        $errors = $this->validateDistributionRequest($allocations, $errors);


        if ($errors) {
            return response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
        }
        // 2. Simpan ke Database dengan Queue
        return $this->execution();
    }

    public function validateAllocationRequest($allocations, $errors)
    {
        $this->allocationRequest = [
            'letter_number' => $allocations[1][0],
            'letter_date' => Carbon::instance(Date::excelToDateTimeObject($allocations[2][0])),
            'type' => 'vaccine',
            'applicant_name' => $allocations[3][0],
            'applicant_position' => $allocations[4][0],
            'applicant_agency_id' => $allocations[5][0],
            'applicant_agency_name' => $allocations[6][0],
            'distribution_description' => $allocations[7][0],
            'letter_url' => $allocations[8][0],
            'status' => AllocationRequestStatusEnum::success()
        ];

        $validator = Validator::make($this->allocationRequest, $this->allocationRequestRule);
        if ($validator->fails()) {
            $errors['allocation_request'] = $validator->errors()->messages();
        }

        return $errors;
    }

    public function validateDistributionRequest($allocations, $errors)
    {
        for ($index = 15; $index < count($allocations); $index++) {
            if (!$allocations[$index][1]) {
                continue;
            }

            $allocationDistribution = [
                'agency_id' => $allocations[$index][1],
                'agency_name' => $allocations[$index][0],
                'distribution_plan_date' => Carbon::instance(Date::excelToDateTimeObject($allocations[$index][8])),
                'allocation_material_requests' => []
            ];

            $validator = Validator::make($allocationDistribution, $this->allocationDistributionRule);
            if ($validator->fails()) {
                $errors['allocation_distributions'][] = $validator->errors()->messages();
            }

            $errors = $this->validateMaterialRequest($allocations, $errors, $index, $allocationDistribution);
        }

        return $errors;
    }

    public function validateMaterialRequest($allocations, $errors, $index, $allocationDistribution)
    {
        for ($column = 0; $column < count($allocations[10]) - 1; $column++) {
            $isMaterialID = strpos($allocations[10][$column], 'MAT-') !== false;
            if (!$isMaterialID) {
                continue;
            }

            $allocationMaterialRequest = [
                'matg_id' => $allocations[11][$column],
                'material_id' => $allocations[10][$column],
                'material_name' => $allocations[12][$column],
                'qty' => $allocations[$index][$column] ?? 0
            ];

            $validator = Validator::make($allocationMaterialRequest, $this->allocationMaterialRequestRule);
            if ($validator->fails()) {
                $errors['allocation_material_requests'][] = $validator->errors()->messages();
            }

            $allocationDistribution['allocation_material_requests'][] = $allocationMaterialRequest;
        }

        $this->arrAllocationDistribution[] = $allocationDistribution;

        return $errors;
    }

    public function execution()
    {
        // 2. Simpan ke Database dengan Queue
        DB::beginTransaction();
        try {
            $allocationRequest = AllocationRequest::create($this->allocationRequest);

            $material = [];
            foreach ($this->arrAllocationDistribution as $allocationDistribution) {
                $allocationDistribution['allocation_request_id'] = $allocationRequest->id;
                $allocationDistributionRequest = AllocationDistributionRequest::create($allocationDistribution);

                $distributionID = $allocationDistributionRequest->id;
                foreach ($allocationDistribution['allocation_material_requests'] as $key => $allocation_material_requests) {
                    $allocationDistribution['allocation_material_requests'][$key]['allocation_request_id'] = $allocationRequest->id;
                    $allocationDistribution['allocation_material_requests'][$key]['allocation_distribution_request_id'] = $distributionID;
                    $allocationDistribution['allocation_material_requests'][$key]['created_at'] = Carbon::now();

                    $UoM = AllocationMaterial::where('material_id', $allocation_material_requests['material_id'])->value('UoM');
                    $allocationDistribution['allocation_material_requests'][$key]['UoM'] = $UoM;

                    $material[] = $allocationDistribution['allocation_material_requests'][$key];
                }
            }

            AllocationMaterialRequest::insert($material);

            DB::commit();
            return response()->format(Response::HTTP_OK, 'success');
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception->getTrace());
        }
    }
}
