<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $table = 'letter';

    protected $fillable = [
        'agency_id',
        'applicant_id',
        'letter'
    ];
}
