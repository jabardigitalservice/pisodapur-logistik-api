<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalFacility extends Model
{
    protected $fillable = [
        'name', 'medical_facility_type_id', 'city_id', 'district_id', 'village_id', 'address', 'phone', 'poslog_id', 'poslog_name'
    ];

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
