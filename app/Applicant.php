<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $fillable = [
        'agency_id',
        'applicant_name',
        'applicants_office',
        'file',
        'email',
        'primary_phone_number',
        'secondary_phone_number'
    ];

    public function agency()
    {
        return $this->belongsToOne('App\Agency', 'id', 'agency_id');
    }
}
