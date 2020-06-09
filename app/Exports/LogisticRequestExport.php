<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Agency;
use DB;

class LogisticRequestExport implements FromQuery, WithMapping, WithHeadings, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $request;

    function __construct($request) {
           $this->request = $request;
    }
    public function query()
    {

        DB::statement(DB::raw('set @row:=0'));
        $data = Agency::selectRaw('*, @row:=@row+1 as row_number')
        ->with([
            'masterFaskesType' => function ($query) {
                return $query->select(['id', 'name']);
            },
            'applicant' => function ($query) {
                return $query->select([
                    'id', 'agency_id', 'applicant_name', 'applicant_name', 'applicants_office', 'file', 'email', 'primary_phone_number', 'secondary_phone_number', 'verification_status'
                ]);
            },
            'city' => function ($query) {
                return $query->select(['kemendagri_kabupaten_kode', 'kemendagri_kabupaten_nama']);
            },
            'subDistrict' => function ($query) {
                return $query->select(['kemendagri_kecamatan_kode', 'kemendagri_kecamatan_nama']);
            },
            'village' => function ($query) {
                return $query->select(['kemendagri_desa_kode', 'kemendagri_desa_nama']);
            },
            'logisticRequestItems' => function ($query) {
                return $query->select(['agency_id', 'product_id', 'brand', 'quantity', 'unit', 'usage', 'priority']);
            },
            'logisticRequestItems.product' => function ($query) {
                return $query->select(['id', 'name', 'material_group_status', 'material_group']);
            },
            'logisticRequestItems.masterUnit' => function ($query) {
                return $query->select(['id', 'unit as name']);
            }
        ])->whereHas('applicant', function ($query){
            if ($this->request->verification_status) {
                $query->where('verification_status', $this->request->verification_status);
            }

            if ($this->request->date) {
                $query->whereRaw('DATE(created_at) = ?', [$this->request->date]);
            }
            if ($this->request->source_data) {
                $query->where('source_data', $this->request->source_data);
            }
        })
        ->whereHas('masterFaskesType', function ($query){
            if ($this->request->faskes_type) {
                $query->where('id', $this->request->faskes_type);
            }
        })
        ->where(function ($query){
            if ($this->request->agency_name) {
                $query->where('agency_name', 'LIKE', "%{$this->request->agency_name}%");
            }

            if ($this->request->city_code) {
                $query->where('location_district_code', $this->request->city_code);
            }
        });
        return $data;
    }

    public function headings(): array
    {
        return [
            ['DAFTAR PERMOHONAN LOGISTIK'],
            ['ALAT KESEHATAN'],
            [], //add empty row
            ['Nomor', 'Tanggal Pengajuan', 'Jenis Instansi', 'Nomor Telp Instansi', 'Alamat Lengkap', 'Kab/Kota', 'Kecamatan', 'Desa/Kel', 'Nama Pemohon', 'Jabatan', 'Email', 'Nomor Kontak Pemohon (opsi 1)', 'Nomor Kontak Pemohon (opsi 2)', 'Detail Permohonan (Nama Barang, Jumlah dan Satuan, Urgensi)', 'Status Permohonan']
        ];
    }

    /**
     * Map each row
     *
     * @var LogisticsRequest $logisticsRequest
     */
    public function map($logisticsRequest): array
    {
        return [
            $logisticsRequest->row_number,
            $logisticsRequest->created_at,
            $logisticsRequest->masterFaskesType['name'],
            $logisticsRequest->phone_number,
            $logisticsRequest->location_address,
            $logisticsRequest->city['kemendagri_kabupaten_nama'],
            $logisticsRequest->subDistrict['kemendagri_kecamatan_nama'],
            $logisticsRequest->village['kemendagri_desa_nama'],
            $logisticsRequest->applicant['applicant_name'],
            $logisticsRequest->applicant['applicants_office'],
            $logisticsRequest->applicant['email'],
            $logisticsRequest->applicant['primary_phone_number'],
            $logisticsRequest->applicant['secondary_phone_number'],
            $logisticsRequest->logisticRequestItems->map(function ($items){
                if ($items->quantity == '-' && $items->masterUnit->name == '-') {
                    $items->quantityUnit = 'jumlah dan satuan tidak ada';
                } else {
                    $items->quantity = $items->quantity == '-' ? 'jumlah tidak ada ' : $items->quantity;
                    $items->unit = $items->masterUnit->name == '-' ? ' satuan tidak ada' : $items->masterUnit->name;
                    $items->quantityUnit = $items->quantity . ' ' . $items->unit;
                }
                return
                    implode([$items->product->name,
                    $items->quantityUnit,
                    $items->priority == '-' ? 'urgensi tidak ada' : $items->priority], ', ');
            })->implode('; ', ''),
            $logisticsRequest->applicant['verification_status']
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        $styleArray = [
            'font' => [
            'bold' => true,
            ]
        ];
        return [
            AfterSheet::class    => function(AfterSheet $event) use ($styleArray){
                $cellRange = 'A1:O4'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);
                $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray);
                $event->sheet->mergeCells('A1:O1');
            },
        ];
    }
}
