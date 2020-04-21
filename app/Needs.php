<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Needs extends Model
{
    protected $fillable = [
        'agency_id',
        'applicant_id',
        'product_id',
        'item',
        'brand',
        'quantity',
        'unit',
        'usage',
        'priority'
    ];

    public function agency()
    {
        return $this->belongsToMany('App\Agency', 'id', 'agency_id');
    }

    public function product()
    {
        return $this->hasOne('App\Product', 'id', 'product_id');
    }

    public function unit()
    {
        return $this->hasOne('App\MasterUnit', 'id', 'unit');
    }
}
