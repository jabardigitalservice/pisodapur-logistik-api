<?php

namespace App\Imports;

use Illuminate\Database\Eloquent\Model;
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
use PhpOffice\PhpSpreadsheet\Shared\Date;
use JWTAuth;

class LogisticImport extends Model
{
    public static function import($data)
    {
        $user = JWTAuth::user();
        $application = $data->sheetData[0]->toArray();

        foreach ($application as $item) {
            if (isset($item['id_permohonan']) && isset($item['tanggal_pengajuan']) && isset($item['jenis_instansi']) && isset($item['nama_instansi'])) {

                $masterFaskesTypeId = self::getMasterFaskesType($item);
                $item['master_faskes_type_id'] = $masterFaskesTypeId;
                $masterFaskesId = self::getMasterFaskes($item);
                $districtCityId = self::getDistrictCity($item);
                $subDistrictId = self::getSubDistrict($item);
                $villageId = self::getVillage($item);
                $products = self::findProductInSheet($data, $item['id_permohonan']);

                $createdAt = Date::excelToDateTimeObject($item['tanggal_pengajuan']);

                $agency = Agency::create([
                    'master_faskes_id' => $masterFaskesId,
                    'agency_type' => $masterFaskesTypeId,
                    'agency_name' => $item['nama_instansi'] ? $item['nama_instansi'] : '-',
                    'phone_number' => $item['nomor_telepon'] ? $item['nomor_telepon'] : '-',
                    'location_district_code' => $districtCityId ? $districtCityId : '-',
                    'location_subdistrict_code' => $subDistrictId ? $subDistrictId : '-',
                    'location_village_code' => $villageId ? $villageId : '-',
                    'location_address' => $item['alamat'] ? $item['alamat'] : '-',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt
                ]);

                $applicant = Applicant::create([
                    'agency_id' => $agency->id,
                    'applicant_name' => $item['nama_pemohon'] ? $item['nama_pemohon'] : '-',
                    'applicants_office' => $item['jabatan_pemohon'] ? $item['jabatan_pemohon'] : '-',
                    'file' => self::getFileUpload($item['ktp']),
                    'email' => $item['email_pemohon'] ? $item['email_pemohon'] : '-',
                    'primary_phone_number' => $item['nomor_telepon_pemohon_1'] ? $item['nomor_telepon_pemohon_1'] : '-',
                    'secondary_phone_number' => $item['nomor_telepon_pemohon_2'] ? $item['nomor_telepon_pemohon_2'] : '-',
                    'verification_status' => $item['status_verifikasi'],
                    'source_data' => $item['source_data'],
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'verified_by' => $user->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt
                ]);

                $letter = Letter::create([
                    'agency_id' => $agency->id,
                    'applicant_id' => $applicant->id,
                    'letter' => self::getFileUpload($item['surat_permohonan'])
                ]);

                if (count($products) > 0) {
                    foreach ($products as $product) {
                        $need = Needs::create(
                            [
                                'agency_id' => $agency->id,
                                'applicant_id' => $applicant->id,
                                'product_id' => $product['product_id'],
                                'brand' => $product['deskripsi_produk'],
                                'quantity' => $product['jumlah'],
                                'unit' => $product['unit_id'],
                                'usage' => $product['kegunaan'],
                                'priority' => $product['urgensi']
                            ]
                        );
                    }
                }
            }
        }
    }

    public static function getMasterFaskesType($data)
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

    public static function getMasterFaskes($data)
    {
        $masterFaskes = MasterFaskes::where('nama_faskes', 'LIKE', "%{$data['nama_instansi']}%")->first();
        if (!$masterFaskes) {
            $masterFaskes = MasterFaskes::create([
                'id_tipe_faskes' => $data['master_faskes_type_id'],
                'verification_status' => 'verified',
                'nama_faskes' => $data['nama_instansi'],
                'nama_atasan' => '-',
                'nomor_registrasi' => '-',
                'verification_status' => 'verified',
                'is_imported' => true
            ]);
        }
        return $masterFaskes->id;
    }

    public static function getDistrictCity($data)
    {
        $city = City::where('kemendagri_kabupaten_nama', 'LIKE', "%{$data['kabupaten']}%")->first();
        if ($city) {
            return $city->kemendagri_kabupaten_kode;
        }
        return false;
    }

    public static function getSubDistrict($data)
    {
        $subDistrict = Subdistrict::where('kemendagri_kecamatan_nama', 'LIKE', "%{$data['kecamatan']}%")->first();
        if ($subDistrict) {
            return $subDistrict->kemendagri_kecamatan_kode;
        }
        return false;
    }

    public static function getVillage($data)
    {
        $village = Village::where('kemendagri_desa_nama', 'LIKE', "%{$data['desa']}%")->first();
        if ($village) {
            return $village->kemendagri_desa_kode;
        }
        return false;
    }

    public static function getFileUpload($file)
    {
        $fileUpload = FileUpload::create(['name' => $file]);
        return $fileUpload->id;
    }

    public static function findProductInSheet($data, $idPermohonan)
    {
        $logisticItem = $data->sheetData[1]->toArray();
        $result = [];

        foreach ($logisticItem as $item) {
            if (isset($item['id_permohonan'])) {
                if ($item['id_permohonan'] === $idPermohonan) {
                    $productId = self::getProduct($item);
                    $item['product_id'] = $productId;
                    $unitId = self::getMasterUnit($item);
                    $item['unit_id'] = $unitId;
                    $result[] = $item;
                }
            }
        }
        return $result;
    }

    public static function getProduct($data)
    {
        $product = Product::where('name', 'LIKE', "%{$data['nama_produk']}%")->first();
        if (!$product) {
            $product = Product::create([
                'name' => $data['nama_produk'],
                'is_imported' => true
            ]);
        }

        return $product->id;
    }

    public static function getMasterUnit($data)
    {
        $masterUnit = MasterUnit::where('unit', 'LIKE', "%{$data['satuan']}%")->first();
        if (!$masterUnit) {
            $masterUnit = MasterUnit::create([
                'unit' => ucwords($data['satuan']),
                'is_imported' => true,
            ]);

            ProductUnit::create([
                'product_id' => $data['product_id'],
                'unit_id' => $masterUnit->id
            ]);
        }

        return $masterUnit->id;
    }
}
