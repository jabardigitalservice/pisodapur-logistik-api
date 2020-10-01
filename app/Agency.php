<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $table = 'agency';

    protected $fillable = [
        'master_faskes_id',
        'agency_type',
        'agency_name',
        'phone_number',
        'location_district_code',
        'location_subdistrict_code',
        'location_village_code',
        'location_address'
    ];

    public function masterFaskesType()
    {
        return $this->hasOne('App\MasterFaskesType', 'id', 'agency_type');
    }

    public function applicant()
    {
        return $this->hasOne('App\Applicant', 'agency_id', 'id');
    }

    public function letter()
    {
        return $this->hasOne('App\Letter', 'agency_id', 'id');
    }

    public function need()
    {
        return $this->hasMany('App\Needs', 'agency_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo('App\City', 'location_district_code', 'kemendagri_kabupaten_kode');
    }

    public function village()
    {
        return $this->belongsTo('App\Village', 'location_village_code', 'kemendagri_desa_kode');
    }

    public function subDistrict()
    {
        return $this->belongsTo('App\Subdistrict', 'location_subdistrict_code', 'kemendagri_kecamatan_kode');
    }

    public function logisticRequestItems()
    {
        return $this->need();
    }

    public function logisticRealizationItems()
    {
        return $this->hasMany('App\LogisticRealizationItems', 'agency_id', 'id');
    }

    public function recommendationItems()
    {
        return $this->logisticRealizationItems();
    }

    public function finalizationItems()
    {
        return $this->logisticRealizationItems();
    }

    public function tracking()
    {
        return $this->hasOne('App\Applicant', 'agency_id', 'id');
    }
}
