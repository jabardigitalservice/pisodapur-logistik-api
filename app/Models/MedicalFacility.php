<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalFacility extends Model
{
    //

    public function city()
    {
        return $this->hasOne('\App\City', 'kemendagri_kabupaten_kode', 'city_id');
    }

    public function district()
    {
        return $this->hasOne('\App\Subdistrict', 'kemendagri_kecamatan_kode', 'district_id');
    }

    public function village()
    {
        return $this->hasOne('\App\Village', 'kemendagri_desa_kode', 'village_id');
    }
}
