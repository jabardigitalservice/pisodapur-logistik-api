<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

use App\Usage;

class RecipientFaskesExport implements FromArray, WithHeadings
{
    use Exportable;

    public function array(): array
    {
        list($err, $faskes_list) = Usage::getPelaporanFaskesSummary();
        if ($err != null) { //error
            return $err;
        }

        return $faskes_list;
    }

    public function headings(): array
    {
        return [
            "Nama Faskes",
            "Stok Tersedia",
            "Stok Terpakai"
        ];
    }

}
