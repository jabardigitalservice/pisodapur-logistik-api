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
use DB;

class LogisticRequestImport implements ToCollection, WithStartRow
{
    protected $result = [];
    public $data;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $data = $row->toArray();
            $createdAt = gmdate("Y-m-d H:i:s", ($data[0] - 25569) * 86400);

            $masterFaskesTypeId = $this->getMasterFaskesType($data);
            $masterFaskesId = $this->getMasterFaskes($data);
            $districtCityId = $this->getDistrictCity($data);
            $subDistrictId = $this->getSubDistrict($data);
            $villageId = $this->getVillage($data);
            $logisticList = $this->getLogisticList($data);
            $totaldelimiter = substr_count($data[15], '#');

            DB::beginTransaction();
            try {
                if ($data[2]) {
                    if ($masterFaskesTypeId != null) {
                        if ($masterFaskesId != null) {
                            if ($logisticList != null) {
                                if (($totaldelimiter % count($logisticList)) == 0) {
                                    $agency = Agency::create([
                                        'master_faskes_id' => $masterFaskesId,
                                        'agency_type' => $masterFaskesTypeId,
                                        'agency_name' => $data[2],
                                        'phone_number' => $data[3],
                                        'location_district_code' => $districtCityId,
                                        'location_subdistrict_code' => $subDistrictId,
                                        'location_village_code' => $villageId,
                                        'location_address' => $data[7],
                                        'created_at' => $createdAt,
                                        'updated_at' => $createdAt
                                    ]);

                                    $applicant = Applicant::create([
                                        'agency_id' => $agency->id,
                                        'applicant_name' => $data[8],
                                        'applicants_office' => $data[9],
                                        'file' => $this->getFileUpload($data[13]),
                                        'email' => $data[10],
                                        'primary_phone_number' => $data[11],
                                        'secondary_phone_number' => $data[12],
                                        'verification_status' => $data[16],
                                        'created_at' => $createdAt,
                                        'updated_at' => $createdAt
                                    ]);

                                    $letter = Letter::create([
                                        'agency_id' => $agency->id,
                                        'applicant_id' => $applicant->id,
                                        'letter' => $this->getFileUpload($data[14])
                                    ]);

                                    foreach ($logisticList as $logisticItem) {
                                        $unitId = $this->getMasterUnit($logisticItem);
                                        $need = Needs::create(
                                            [
                                                'agency_id' => $agency->id,
                                                'applicant_id' => $applicant->id,
                                                'product_id' => $logisticItem['product_id'],
                                                'brand' => $logisticItem[1],
                                                'quantity' => $logisticItem[2],
                                                'unit' => $unitId,
                                                'usage' => $logisticItem[4],
                                                'priority' => $logisticItem[5]
                                            ]
                                        );
                                    }
                                    $data['status'] = 'valid';
                                    $data['notes'] = '';
                                    $this->result[] = $data;
                                } else {
                                    $data['status'] = 'invalid';
                                    $data['notes'] = 'Format list logistik kesehatan tidak tidak sesuai';
                                    $this->result[] = $data;
                                }
                            } else {
                                $data['status'] = 'invalid';
                                $data['notes'] = 'Logistik kesehatan tidak terdaftar di data master';
                                $this->result[] = $data;
                            }
                        } else {
                            $data['status'] = 'invalid';
                            $data['notes'] = 'Nama instansi tidak terdaftar di data master';
                            $this->result[] = $data;
                        }
                    } else {
                        $data['status'] = 'invalid';
                        $data['notes'] = 'Jenis instansi tidak terdaftar di data master';
                        $this->result[] = $data;
                    }
                }

                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
            }
        }

        $this->data = $this->result;
    }

    public function getMasterFaskesType($data)
    {
        $masterFaskesType = MasterFaskesType::where('name', 'LIKE', "%{$data[1]}%")->first();
        if ($masterFaskesType) {
            return $masterFaskesType->id;
        }
        return false;
    }

    public function getMasterFaskes($data)
    {
        $masterFaskes = MasterFaskes::where('nama_faskes', 'LIKE', "%{$data[2]}%")->first();
        if ($masterFaskes) {
            return $masterFaskes->id;
        }
        return false;
    }

    public function getDistrictCity($data)
    {
        $city = City::where('kemendagri_kabupaten_nama', 'LIKE', "%{$data[4]}%")->first();
        if ($city) {
            return $city->kemendagri_kabupaten_kode;
        }
        return false;
    }

    public function getSubDistrict($data)
    {
        $subDistrict = Subdistrict::where('kemendagri_kecamatan_nama', 'LIKE', "%{$data[5]}%")->first();
        if ($subDistrict) {
            return $subDistrict->kemendagri_kecamatan_kode;
        }
        return false;
    }

    public function getVillage($data)
    {
        $village = Village::where('kemendagri_desa_nama', 'LIKE', "%{$data[6]}%")->first();
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
        $logisticListArray = explode('&&', $data[15]);
        foreach ($logisticListArray as $logisticListItem) {
            $logisticList1[] = explode('#', $logisticListItem);
        }

        foreach ($logisticList1 as $logisticItem) {
            if ($this->getProduct($logisticItem)) {
                $logisticItem['product_id'] = $this->getProduct($logisticItem);
                $logisticList2[] = $logisticItem;
            } else {
                return false;
            }
        }

        return $logisticList2;
    }

    public function getProduct($data)
    {
        $product = Product::where('name', 'LIKE', "%{$data[0]}%")->first();

        if ($product) {
            return $product->id;
        }
        return false;
    }

    public function getMasterUnit($data)
    {
        $masterUnit = MasterUnit::where('unit', 'LIKE', "%{$data[3]}%")->first();

        if (!$masterUnit) {
            $masterUnit = MasterUnit::create([
                'unit' => ucwords($data[3])
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
        return 8;
    }
}
