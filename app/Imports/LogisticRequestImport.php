<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Agency;
use App\Applicant;
use App\Letter;
use App\FileUpload;
use App\Needs;
use App\MasterFaskesType;
use App\MasterFaskes;
use App\City;
use App\Subdistrict;
use App\Village;
use App\Product;
use App\MasterUnit;
use App\ProductUnit;
use App\Imports\LogisticImport;
use DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class LogisticRequestImport implements ToCollection, WithStartRow
{
    protected $result = [];
    protected $invalidFormatLogistic = [];
    protected $invalidItemLogistic = [];
    public $data;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $dataImport = [
                'tanggal_pengajuan' => $row[0],
                'jenis_instansi' => $row[1],
                'nama_instansi' => $row[2],
                'telepon_instansi' => $row[3],
                'kabupaten' => $row[4],
                'kecamatan' => $row[5],
                'desa' => $row[6],
                'alamat' => $row[7],
                'nama_pemohon' => $row[8],
                'jabatan_pemohon' => $row[9],
                'email_pemohon' => $row[10],
                'telepon_pemohon_1' => $row[11],
                'telepon_pemohon_2' => $row[12],
                'file_ktp' => $row[13],
                'file_surat_permohonan' => $row[14],
                'list_logistik' => $row[15],
                'status_verifikasi' => $row[16]
            ];
            $dataSet = $this->setData($dataImport);
            $dataImport['master_faskes_type_id'] = $dataSet['masterFaskesTypeId'];

            if ($dataImport['tanggal_pengajuan'] && $dataImport['jenis_instansi'] && $dataImport['nama_instansi']) {
                $this->validatingDataImport($dataSet, $dataImport);
            }
        }

        $this->data = $this->result;
    }

    public function getMasterFaskesType($data)
    {
        $masterFaskesType = MasterFaskesType::where('name', 'LIKE', "%{$data['jenis_instansi']}%")->first();
        if (!$masterFaskesType) {
            $masterFaskesType = MasterFaskesType::create([
                'name' => $data['jenis_instansi'],
                'is_imported' => true
            ]);
        }
        return $masterFaskesType->id;
    }

    public function getDistrictCity($data)
    {
        $city = City::where('kemendagri_kabupaten_nama', 'LIKE', "%{$data['kabupaten']}%")->first();
        if ($city) {
            return $city->kemendagri_kabupaten_kode;
        }
        return false;
    }

    public function getSubDistrict($data)
    {
        $subDistrict = Subdistrict::where('kemendagri_kecamatan_nama', 'LIKE', "%{$data['kecamatan']}%")->first();
        if ($subDistrict) {
            return $subDistrict->kemendagri_kecamatan_kode;
        }
        return false;
    }

    public function getVillage($data)
    {
        $village = Village::where('kemendagri_desa_nama', 'LIKE', "%{$data['desa']}%")->first();
        if ($village) {
            return $village->kemendagri_desa_kode;
        }
        return false;
    }

    public function getFileUpload($file)
    {
        $fileUpload = FileUpload::create(['name' => $file]);
        return $fileUpload->id;
    }

    public function getLogisticList($data)
    {
        $logisticList1 = [];
        $logisticList2 = [];
        $logisticListArray = explode('&&', $data['list_logistik']);
        foreach ($logisticListArray as $logisticListItem) {
            $logisticList1[] = explode('#', $logisticListItem);
        }

        foreach ($logisticList1 as $logisticItem) {
            if (count($logisticItem) == 6) {
                $product = $this->getProduct($logisticItem);
                if ($product) {
                    $logisticItem['product_id'] = $product->id;
                }
                $logisticList2[] = $logisticItem;
            } else {
                $this->invalidFormatLogistic[] = 'cek kembali tanda "#" pada item logistik ' . $logisticItem[0];
            }
        }

        return $logisticList2;
    }

    public function getProduct($data)
    {
        $productName = str_replace(' ', '', $data[0]);
        $product = Product::whereRaw("REPLACE(`name`, ' ', '') LIKE ? ", "%" . $productName . "%")->first();
        if (!$product) {
            $product = Product::create([
                'name' => $productName,
                'material_group_status' => 1,
                'is_imported' => true
            ]);
        }

        return $product;
    }

    public function getMasterUnit($data)
    {
        $masterUnit = MasterUnit::where('unit', 'LIKE', "%{$data[3]}%")->first();

        if (!$masterUnit) {
            $masterUnit = MasterUnit::create([
                'unit' => ucwords($data[3]),
                'is_imported' => true,
            ]);

            ProductUnit::create([
                'product_id' => $data['product_id'],
                'unit_id' => $masterUnit->id
            ]);
        }

        return $masterUnit->id;
    }

    public function startRow(): int
    {
        return 2;
    }
    
    public function setData($dataImport)
    {
        $ret['createdAt'] = Date::excelToDateTimeObject($dataImport['tanggal_pengajuan']);
        $ret['masterFaskesTypeId'] = $this->getMasterFaskesType($dataImport);
        $dataImport['master_faskes_type_id'] = $ret['masterFaskesTypeId'];
        $ret['masterFaskesId'] = LogisticImport::getMasterFaskes($dataImport);
        $ret['districtCityId'] = $this->getDistrictCity($dataImport);
        $ret['subDistrictId'] = $this->getSubDistrict($dataImport);
        $ret['villageId'] = $this->getVillage($dataImport);
        $ret['logisticList'] = $this->getLogisticList($dataImport);

        return $ret;
    }

    public function validatingDataImport($dataSet, $dataImport)
    {
        $dataImport['status'] = 'invalid';
        if (!$dataSet['masterFaskesTypeId']) {
            $dataImport['notes'] = 'Jenis instansi tidak terdaftar di data master';
        } else if (!$dataSet['masterFaskesId']) {
            $dataImport['notes'] = 'Nama instansi tidak terdaftar di data master';
        } else if (count($this->invalidFormatLogistic) > 0) {
            $dataImport['notes'] = implode(",", $this->invalidFormatLogistic);
            $this->result[] = $dataImport;
        } else if (count($this->invalidItemLogistic) > 0) {
            $dataImport['notes'] = implode(",", $this->invalidItemLogistic);
        } else {
            $this->insertData($dataSet, $dataImport);
            $dataImport['status'] = 'valid';
            $dataImport['notes'] = '';
        }
        $this->result[] = $dataImport;
        $this->invalidItemLogistic = [];
        $this->invalidFormatLogistic = [];
    }

    public function insertData($dataSet, $dataImport)
    {
        $agency = Agency::create($this->setAgencyData($dataSet, $dataImport));
        $dataSet['agency_id'] = $agency->id;
        $applicant = Applicant::create($this->setApplicantData($dataSet, $dataImport));
        $dataSet['applicant_id'] = $applicant->id;

        $letter = Letter::create($this->setLetterData($dataSet, $dataImport));

        foreach ($dataSet['logisticList'] as $logisticItem) {
            $unitId = $this->getMasterUnit($logisticItem);
            $need = Needs::create([
                'agency_id' => $agency->id,
                'applicant_id' => $applicant->id,
                'product_id' => $logisticItem['product_id'],
                'brand' => $logisticItem[1],
                'quantity' => $logisticItem[2],
                'unit' => $unitId,
                'usage' => $logisticItem[4],
                'priority' => $logisticItem[5]
            ]);
        }
    }

    public function setAgencyData($dataSet, $dataImport)
    {
        $result = [
            'master_faskes_id' => $dataSet['masterFaskesId'],
            'agency_type' => $dataSet['masterFaskesTypeId'],
            'agency_name' => $dataImport['nama_instansi'] ? $dataImport['nama_instansi'] : '-',
            'phone_number' => $dataImport['telepon_instansi'] ? $dataImport['telepon_instansi'] : '-',
            'location_district_code' => $dataSet['districtCityId'] ? $dataSet['districtCityId'] : '-',
            'location_subdistrict_code' => $dataSet['subDistrictId'] ? $dataSet['subDistrictId'] : '-',
            'location_village_code' => $dataSet['villageId'] ? $dataSet['villageId'] : '-',
            'location_address' => $dataImport['alamat'] ? $dataImport['alamat'] : '-',
            'created_at' => $dataSet['createdAt'],
            'updated_at' => $dataSet['createdAt']
        ];
        return $result;
    }

    public function setApplicantData($dataSet, $dataImport)
    {
        $result = [
            'agency_id' => $dataSet['agency_id'],
            'applicant_name' => $dataImport['nama_pemohon'] ? $dataImport['nama_pemohon'] : '-',
            'applicants_office' => $dataImport['jabatan_pemohon'] ? $dataImport['jabatan_pemohon'] : '-',
            'file' => $this->getFileUpload($dataImport['file_ktp']),
            'email' => $dataImport['email_pemohon'] ? $dataImport['email_pemohon'] : '-',
            'primary_phone_number' => $dataImport['telepon_pemohon_1'] ? $dataImport['telepon_pemohon_1'] : '-',
            'secondary_phone_number' => $dataImport['telepon_pemohon_2'] ? $dataImport['telepon_pemohon_2'] : '-',
            'verification_status' => $dataImport['status_verifikasi'],
            'created_at' => $dataSet['createdAt'],
            'updated_at' => $dataSet['createdAt']
        ];
        return $result;
    }

    public function setLetterData($dataSet, $dataImport)
    {
        $result = [
            'agency_id' => $dataSet['agency_id'],
            'applicant_id' => $dataSet['applicant_id'],
            'letter' => $this->getFileUpload($dataImport['file_surat_permohonan'])
        ];
        return $result;
    }
}
