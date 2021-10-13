<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportExcelRequest;
use App\Imports\MultipleSheetImport;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class ImportAllocationVaccineRequestController extends Controller
{

    /**
     * Import Register Mandiri
     *
     */
    public function import(ImportExcelRequest $request)
    {
        try {
            $import = new MultipleSheetImport();
            $importExcel = Excel::import($import, request()->file('file'));
            $allocations = $import->sheetData[2]->toArray();

            $allocationRequest = [
                'letter_number' => $allocations[1]['0'],
                'letter_date' => $allocations[2]['0'],
                'applicant_name' => $allocations[3]['0'],
                'applicant_position' => $allocations[4]['0'],
                'applicant_agency_id' => $allocations[5]['0'],
                'applicant_agency_name' => $allocations[6]['0'],
                'distribution_description' => $allocations[7]['0'],
                'letter_url' => $allocations[8]['0'],
            ];

            dd($allocations[21]);

            return response()->format(Response::HTTP_OK, 'success', $allocations);
        } catch (\Exception $exception) {
            return response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage());
        }
    }
}
