<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterFaskesType extends Model
{
    protected $table = 'master_faskes_types';
    protected $fillable = ['name', 'is_imported', 'non_public'];

    const HEALTH_FACILITY = [1, 2, 3]; //1. Rumah Sakit, 2. Puskesmas, 3. Klinik
    const NON_HEALTH_FACILITY = [4, 5]; //4. Masyarakat Umum, 5. Instansi Lainnya

    public function masterFaskes()
    {
        return $this->belongsToOne('App\MasterFaskes', 'id_tipe_faskes');
    }

    public function agency()
    {
        return $this->hasMany('App\Agency', 'agency_type');
    }
}
