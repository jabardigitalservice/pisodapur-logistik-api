<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * id                        primary key
 * kemendagri_kabupaten_nama string
 * kemendagri_kabupaten_kode string
 * kemendagri_provinsi_nama  string
 * kemendagri_provinsi_kode  string
 * dinkes_kota_kode          string
 */
class City extends Model {

	protected $table = 'districtcities';

    public function agency()
    {
        return $this->hasMany('App\Agency', 'location_district_code', 'kemendagri_kabupaten_kode');
    }
}
