<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Aplicant extends Model
{
    protected $fillable = [
        'agency_id',
        'aplicant_name',
        'aplicants_office',
        'file',
        'email',
        'primary_phone_number',
        'secondary_phone_number'
    ];
}
