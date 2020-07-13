<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Needs extends Model
{
    use SoftDeletes;
    
    const STATUS = [
        'Rendah',
        'Menengah',
        'Tinggi',
    ];

    protected $fillable = [
        'agency_id',
        'applicant_id',
        'product_id',
        'item',
        'brand',
        'quantity',
        'unit',
        'usage',
        'priority',
        'created_by'
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
    
    public function masterUnit()
    {
        return $this->hasOne('App\MasterUnit', 'id', 'unit');
    }
    
}
