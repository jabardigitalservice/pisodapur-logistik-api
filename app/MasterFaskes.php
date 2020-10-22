<?php

namespace App;

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
        'id_tipe_faskes',
        'nama_atasan',
        'longitude',
        'latitude',
        'is_imported',
        'point_latitude_longitude',
        'non_medical'
    ];

    public function masterFaskesType()
    {
        return $this->hasOne('App\MasterFaskesType', 'id', 'id_tipe_faskes');
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
}
