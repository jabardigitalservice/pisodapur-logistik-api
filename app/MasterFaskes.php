<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class MasterFaskes extends Model
{
    const STATUS_NOT_VERIFIED = 'not_verified';
    const STATUS_VERIFIED = 'verified';

    protected $table = 'master_faskes';
    protected $fillable = [
        'nomor_izin_sarana',
        'nomor_registrasi',
        'nama_faskes',
        'poslog_id',
        'poslog_name',
        'id_tipe_faskes',
        'nama_atasan',
        'longitude',
        'latitude',
        'is_imported',
        'point_latitude_longitude',
        'non_medical',
        'kode_kab_kemendagri',
        'kode_kec_kemendagri',
        'kode_kel_kemendagri',
        'nomor_telepon',
        'nama_kab',
        'alamat',
        'is_reference'
    ];

    public function masterFaskesType()
    {
        return $this->hasOne('App\MasterFaskesType', 'id', 'id_tipe_faskes');
    }

    public function village()
    {
        return $this->hasOne('App\Village', 'kemendagri_desa_kode', 'kode_kel_kemendagri');
    }

    public function getVerificationStatusAttribute($value)
    {
        $status = $value === self::STATUS_NOT_VERIFIED ? 'Belum Terverifikasi' : ($value === self::STATUS_VERIFIED ? 'Terverifikasi' : '');
        return $status;
    }

    static function getFaskesName($request)
    {
        $name = $request->agency_name;

        if ($request->agency_type <= 3) {
            $data = self::findOrFail($request->master_faskes_id);
            $name = $data->nama_faskes;
        }
        return $name;
    }

    static function createFaskes(Request $request)
    {
        try {
            $model = new MasterFaskes();
            $model->fill([
                'id_tipe_faskes' => $request->agency_type,
                'nama_faskes' => $request->agency_name
            ]);
            $model->nomor_izin_sarana = '-';
            $model->nama_atasan = '-';
            $model->point_latitude_longitude = '-';
            $model->verification_status = 'verified';
            $model->is_imported = 0;
            $model->non_medical = 1;
            $model->save();
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
        return $model;
    }
}
