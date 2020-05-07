<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterFaskes extends Model
{
    const STATUS_NOT_VERIFIED = 'not_verified';
    const STATUS_VERIFIED = 'verified';

    protected $table = 'master_faskes';
    protected $fillable = [
        'nomor_registrasi',
        'nama_faskes',
        'id_tipe_faskes',
        'nama_atasan',
        'longitude',
        'latitude',
        'is_imported'
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
}
