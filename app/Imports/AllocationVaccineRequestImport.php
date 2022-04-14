<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllocationVaccineRequestImport implements  WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Template Alokasi' => new SecondSheetImport(),
        ];
    }
}
