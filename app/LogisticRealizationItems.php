<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogisticRealizationItems extends Model
{
    const STATUS = [
        'delivered',
        'not_delivered',
        'approved',
        'not_avalivable'
    ];
    const DEFAULT_STATUS = 'not_approved';

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
}
