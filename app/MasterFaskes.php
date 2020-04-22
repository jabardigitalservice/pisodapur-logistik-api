<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterFaskes extends Model
{
    protected $table = 'master_faskes';
    protected $fillable = [
        'nomor_registrasi',
        'nama_faskes',
        'id_tipe_faskes',
        'nama_atasan',
        'longitude',
        'latitude'
    ];

    public function masterFaskesType()
    {
        return $this->hasOne('App\MasterFaskesType', 'id', 'id_tipe_faskes');
    }
}
