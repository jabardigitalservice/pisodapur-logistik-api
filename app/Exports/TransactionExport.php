<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

use App\Transaction;

class TransactionExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    public function query()
    {
        // only display out transaction
        return Transaction::where('quantity','<',0);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Tujuan Distribusi',
            'Nama Pemohon/PIC',
            'Nomor Telepon',
            'Alamat',
            'Kode Kecamatan Alamat',
            'Kode Kabupaten/Kota Alamat',
            'Kode Provinsi Alamat',
            'Jumlah',
            'Waktu',
            'Catatan',
        ];
    }

    /** 
     * Map each row
     *
     * @var Transaction $invoice
     */
    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->name,
            $transaction->contact_person,
            $transaction->phone_number,
            $transaction->location_address,
            $transaction->location_subdistrict_code,
            $transaction->location_district_code,
            $transaction->location_province_code,
            abs($transaction->quantity),
            ($transaction->time != null)?$transaction->time->format('Y-m-d'):'',
            $transaction->note,
        ];
    }

}
