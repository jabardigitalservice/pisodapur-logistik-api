<?php

namespace App\Models\Vaccine;

class Archive extends VaccineRequest
{
    protected $table = 'vaccine_requests';

    protected $with = [
        'bookedBy:id,name', 'doBy:id,name', 'intransitBy:id,name'
    ];

    public function bookedBy()
    {
        return $this->hasOne('App\User', 'id', 'booked_by');
    }

    public function doBy()
    {
        return $this->hasOne('App\User', 'id', 'do_by');
    }

    public function intransitBy()
    {
        return $this->hasOne('App\User', 'id', 'intransit_by');
    }
}
