<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogisticRealizationItems extends Model
{
    const STATUS = [
        'delivered',
        'not_delivered',
        'approved',
        'not_approved',
        'not_avalivable'
    ];

    protected $table = 'logistic_realization_items';

    protected $fillable = [
        'id',
        'agency_id',
        'need_id',
        'product_id',
        'realization_quantity',
        'unit_id',
        'status',
        'realization_date',
        'created_by',
        'updated_by'
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
        return $this->hasOne('App\MasterUnit', 'id', 'unit_id');
    }
}
