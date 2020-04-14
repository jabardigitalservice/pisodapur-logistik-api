<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $table = 'agency';
    protected $fillable = [
        'agency_type',
        'agency_name',
        'phone_number',
        'location_district_code',
        'location_subdistrict_code',
        'location_village_code',
        'location_address'
    ];
}
