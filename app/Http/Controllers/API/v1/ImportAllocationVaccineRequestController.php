<?php

namespace App\Http\Controllers\API\v1;

use App\AllocationDistributionRequest;
use App\AllocationMaterial;
use App\AllocationMaterialRequest;
use App\AllocationRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportExcelRequest;
use App\Imports\MultipleSheetImport;
use App\MasterFaskes;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class ImportAllocationVaccineRequestController extends Controller
{

    /**
     * Import Register Mandiri
     *
     */
    public function import(ImportExcelRequest $request)
    {
        DB::beginTransaction();
        try {
            $import = new MultipleSheetImport();
            $importExcel = Excel::import($import, request()->file('file'));
            $allocations = $import->sheetData[2]->toArray();

            $allocationRequest = AllocationRequest::create([
                'letter_number' => $allocations[1][0],
                'letter_date' => $allocations[2][0],
                'type' => 'vaccine',
                'applicant_name' => $allocations[3][0],
                'applicant_position' => $allocations[4][0],
                'applicant_agency_id' => $allocations[5][0],
                'applicant_agency_name' => $allocations[6][0],
                'distribution_description' => $allocations[7][0],
                'letter_url' => $allocations[8][0],
            ]);

            // Get distribution allocation requests
            $allocationDistributionRequest = [];
            for ($index = 15; $index < count($allocations); $index++) {
                if ($allocations[$index][1]) {
                    $allocationDistributionRequest = AllocationDistributionRequest::create([
                        'allocation_request_id' => $allocationRequest->id,
                        'agency_id' => MasterFaskes::where('poslog_id', $allocations[$index][1])->value('id'),
                        'agency_name' => $allocations[$index][0],
                        'distribution_plan_date' => $allocations[$index][8],
                    ]);

                    for ($column = 0; $column < count($allocations[10])-1; $column++) {
                        if (strpos($allocations[10][$column], 'MAT-') !== false) {
                            $allocationMaterial = AllocationMaterial::where('material_id', $allocations[10][$column])->first();

                            AllocationMaterialRequest::create([
                                'allocation_request_id' => $allocationRequest->id,
                                'allocation_distribution_request_id' => $allocationDistributionRequest->id,
                                'matg_id' => $allocations[11][$column],
                                'material_id' => $allocations[10][$column],
                                'material_name' => $allocations[12][$column],
                                'qty' => $allocations[$index][$column] ?? 0,
                                'UoM' => $allocationMaterial->UoM,
                            ]);
                        }
                    }
                }
            }
            DB::commit();
            return response()->format(Response::HTTP_OK, 'success', $allocations);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception->getTrace());
        }
    }
}
