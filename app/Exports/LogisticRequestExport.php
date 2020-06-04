<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Http\Request;

use App\Transaction;

class LogisticRequestExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    public function query(Request $request)
    {
        dd($request);
        // only display out transaction
        $data = Transaction::with(['city', 'subdistrict'])->where('quantity', '<', 0)->get();

        $data = 1;
        dd($data);


        $angka = 1;
        $angka2 = 5;

        [1, 2, 3, 4];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Tujuan Distribusi',
            'Nama Pemohon/PIC',
            'Nomor Telepon',
            'Alamat',
            'Kecamatan Alamat',
            'Kabupaten/Kota Alamat',
            'Provinsi Alamat',
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
            //$transaction->location_subdistrict_code,
            $transaction->location_subdistrict_name,
            //$transaction->location_district_code,
            $transaction->location_district_name,
            //$transaction->location_province_code,
            $transaction->location_province_name,
            abs($transaction->quantity),
            ($transaction->time != null) ? $transaction->time->format('Y-m-d') : '',
            $transaction->note,
        ];
    }
}
