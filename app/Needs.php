<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Needs extends Model
{
    protected $fillable = [
        'agency_id',
        'aplicant_id',
        'item',
        'brand',
        'quantity',
        'unit',
        'usage',
        'priority'
    ];
}
