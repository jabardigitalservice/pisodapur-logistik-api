<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Agency;
use App\Applicant;
use App\LogisticRealizationItems;
use DB;

class LogisticRequestExport implements FromCollection, WithMapping, WithHeadings, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $request;

    function __construct($request) {
           $this->request = $request;
    }

    public function collection()
    {
        $data = $this->agencyData();
        $data = $this->withApplicantData($data);
        $data = $this->withAreaData($data);
        $data = $this->withLogisticRequestData($data);
        $data = $this->withRecommendationItems($data);
        $data = $this->withFinalizationItems($data);
        $data = $this->whereHasApplicantData($data);
        $data = $this->whereHasApplicantFilterByStatusData($data);
        $data = $this->whereData($data);
        $data = $this->sortingData($data)->get();

        foreach ($data as $key => $value) {
            $data[$key]->row_number = $key + 1;
        }

        return $data;
    }

    public function agencyData()
    {
        return Agency::with([
            'masterFaskesType' => function ($query) {
                return $query->select(['id', 'name']);
            }
        ]);
    }

    public function withAreaData($data)
    {
        return $data->with([
            'city' => function ($query) {
                return $query->select(['kemendagri_kabupaten_kode', 'kemendagri_kabupaten_nama']);
            },
            'subDistrict' => function ($query) {
                return $query->select(['kemendagri_kecamatan_kode', 'kemendagri_kecamatan_nama']);
            },
            'village' => function ($query) {
                return $query->select(['kemendagri_desa_kode', 'kemendagri_desa_nama']);
            }
        ]);
    }

    public function withLogisticRequestData($data)
    {
        return $data->with([
            'logisticRequestItems' => function ($query) {
                return $query->select(['agency_id', 'product_id', 'brand', 'quantity', 'unit', 'usage', 'priority']);
            },
            'logisticRequestItems.product' => function ($query) {
                return $query->select(['id', 'name', 'material_group_status', 'material_group']);
            },
            'logisticRequestItems.masterUnit' => function ($query) {
                return $query->select(['id', 'unit as name']);
            }
        ]);
    }

    public function withApplicantData($data)
    {
        return $data->with([
            'applicant' => function ($query) {
                $query->select([
                    'id', 'agency_id', 'applicant_name', 'applicants_office', 'file', 'email', 'primary_phone_number', 'secondary_phone_number', 'verification_status', 'note', 'approval_status', 'approval_note', 'stock_checking_status', 'application_letter_number', 'verified_by', 'verified_at', 'approved_by', 'approved_at', 
                    DB::raw('concat(approval_status, "-", verification_status) as status'),
                    DB::raw('concat(approval_status, "-", verification_status) as statusDetail'),
                    'finalized_by', 'finalized_at'
                ]);
                $query->where('is_deleted', '!=' , 1);
                $query = $this->withPICData($query);
            }
        ]);
    }

    public function withPICData($query)
    {
        return $query->with([
            'verifiedBy' => function ($query) {
                return $query->select(['id', 'name', 'agency_name', 'handphone']);
            },
            'approvedBy' => function ($query) {
                return $query->select(['id', 'name', 'agency_name', 'handphone']);
            },
            'finalizedBy' => function ($query) {
                return $query->select(['id', 'name', 'agency_name', 'handphone']);
            }
        ]);
    }

    public function withRecommendationItems($data)
    {
        return $data->with([
            'recommendationItems' => function ($query) {
                return $query->whereNotIn('status', [
                    LogisticRealizationItems::STATUS_NOT_AVAILABLE,
                    LogisticRealizationItems::STATUS_NOT_YET_FULFILLED
                ]);
            }
        ]);
    }

    public function withFinalizationItems($data)
    {
        return $data->with([
            'finalizationItems' => function ($query) {
                return $query->whereNotIn('final_status', [
                    LogisticRealizationItems::STATUS_NOT_AVAILABLE,
                    LogisticRealizationItems::STATUS_NOT_YET_FULFILLED
                ]);
            }
        ]);
    }

    public function whereHasApplicantData($data)
    {
        return $data->whereHas('applicant', function ($query){
            $query->where('is_deleted', '!=' , 1);

            if ($this->request->source_data) {
                $query->where('source_data', $this->request->source_data);
            }

            if ($this->request->stock_checking_status) {
                $query->where('stock_checking_status', $this->request->stock_checking_status);
            }

            if ($this->request->date) {
                $query->whereRaw('DATE(created_at) = ?', [$this->request->date]);
            }
        });
    }

    public function whereHasApplicantFilterByStatusData($data)
    {
        return $data->whereHas('applicant', function ($query){
            if ($this->request->is_rejected) {
                $query->where('verification_status', Applicant::STATUS_REJECTED)->orWhere('approval_status', Applicant::STATUS_REJECTED);
            } else {
                if ($this->request->verification_status) {
                    $query->where('verification_status', $this->request->verification_status);
                }

                if ($this->request->approval_status) {
                    $query->where('approval_status', $this->request->approval_status);
                }
            }
        });
    }

    public function whereData($data)
    {
        return $data->where(function ($query){
            if ($this->request->agency_name) {
                $query->where('agency_name', 'LIKE', "%{$this->request->agency_name}%");
            }

            if ($this->request->city_code) {
                $query->where('location_district_code', $this->request->city_code);
            }

            if ($this->request->faskes_type) {
                $query->where('agency_type', $this->request->faskes_type);
            }
        });
    }

    public function sortingData($data)
    {
        $sort = $this->request->filled('sort') ? ['agency_name ' . $this->request->input('sort') . ', ', 'updated_at DESC'] : ['updated_at DESC, ', 'agency_name ASC'];
        return $data->orderByRaw(implode($sort));
    }

    public function headings(): array
    {   
        $columns = [
            'Nomor', 'Nomor Surat Permohonan', 'Tanggal Pengajuan', 'Jenis Instansi', 'Nama Instansi', 'Nomor Telp Instansi', 
            'Alamat Lengkap', 'Kab/Kota', 'Kecamatan', 'Desa/Kel', 'Nama Pemohon', 
            'Jabatan', 'Email', 'Nomor Kontak Pemohon (opsi 1)', 'Nomor Kontak Pemohon (opsi 2)', 'Detail Permohonan (Nama Barang, Jumlah dan Satuan, Urgensi)', 
            'Diverifikasi Oleh', 'Rekomendasi Salur', 'Disetujui Oleh', 'Realisasi Salur', 'Diselesaikan Oleh', 'Status Permohonan'
        ];
        
        return [
            ['DAFTAR PERMOHONAN LOGISTIK'],
            ['ALAT KESEHATAN'],
            [], //add empty row
            $columns
        ];
    }

    /**
     * Map each row
     *
     * @var LogisticsRequest $logisticsRequest
     */
    public function map($logisticsRequest): array
    {        
        $administrationColumn = $this->administrationColumn($logisticsRequest);        
        $logisticRequestColumns = $this->logisticRequestColumn($logisticsRequest);
        $recommendationColumn = $this->recommendationColumn($logisticsRequest);
        $finalizationColumn = $this->finalizationColumn($logisticsRequest);
        $data = array_merge($administrationColumn, $logisticRequestColumns, $recommendationColumn, $finalizationColumn);
        return $data;
    }

    public function administrationColumn($logisticsRequest)
    {
        $data = [
            $logisticsRequest->row_number,
            $logisticsRequest->applicant['application_letter_number'],
            $logisticsRequest->created_at,
            $logisticsRequest->masterFaskesType['name'],
            $logisticsRequest->agency_name,
            $logisticsRequest->phone_number,
            $logisticsRequest->location_address,
            $logisticsRequest->city['kemendagri_kabupaten_nama'],
            $logisticsRequest->subDistrict['kemendagri_kecamatan_nama'],
            $logisticsRequest->village['kemendagri_desa_nama'],
            $logisticsRequest->applicant['applicant_name'],
            $logisticsRequest->applicant['applicants_office'],
            $logisticsRequest->applicant['email'],
            $logisticsRequest->applicant['primary_phone_number'],
            $logisticsRequest->applicant['secondary_phone_number']
        ];
        return $data;
    }

    public function logisticRequestColumn($logisticsRequest)
    {
        $data = [
            $logisticsRequest->logisticRequestItems->map(function ($items){
                $isQuantityEmpty = $items['quantity'] == '-' && $items->masterUnit['name'] == '-';
                if ($isQuantityEmpty) {
                    $items->quantityUnit = 'jumlah dan satuan tidak ada';
                } else {
                    $items['quantity'] = $items['quantity'] == '-' ? 'jumlah tidak ada ' : $items['quantity'];
                    $items['unit'] = $items->masterUnit['name'] == '-' ? ' satuan tidak ada' : $items->masterUnit['name'];
                    $items->quantityUnit = $items['quantity'] . ' ' . $items['unit'];
                }

                $list = [
                    $items->product['name'],
                    $items->quantityUnit,
                    $items['priority'] == '-' ? 'urgensi tidak ada' : $items['priority']
                ];
                return implode($list, ', ');
            })->implode('; ', '')
        ];
        return $data;
    }

    public function recommendationColumn($logisticsRequest)
    {        
        $data = [
            $logisticsRequest->applicant->verifiedBy['name'],
            $logisticsRequest->recommendationItems->map(function ($items){
                $items->quantityUnit = $items['realization_quantity'] . ' ' . $items['realization_unit'];
                return implode([$items->product_name, $items->quantityUnit,], ', ');
            })->implode('; ', '')
        ];

        return $data;
    }
    
    public function finalizationColumn($logisticsRequest)
    {        
        $data = [
            $logisticsRequest->applicant->approvedBy['name'],
            $logisticsRequest->finalizationItems->map(function ($items){
                $items->quantityUnit = $items['final_quantity'] . ' ' . $items['final_unit'];
                return implode([$items->final_product_name, $items->quantityUnit,], ', ');
            })->implode('; ', ''),
            $logisticsRequest->applicant->finalizedBy['name'],
            $logisticsRequest->applicant['status']
        ];

        return $data;
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
            AfterSheet::class => function(AfterSheet $event) use ($styleArray){
                $cellRange = 'A1:V4'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);
                $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray);
                $event->sheet->mergeCells('A1:O1');
            },
        ];
    }
}
