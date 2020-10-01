<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinalizationItems extends Model
{
    use SoftDeletes;
    
    const STATUS = [
        'delivered',
        'not_delivered',
        'approved',
        'not_approved',
        'not_available',
        'replaced',
        'not_yet_fulfilled'
    ];

    const STATUS_DELIVERED = 'delivered';
    const STATUS_NOT_DELIVERED = 'not_delivered';
    const STATUS_APPROVED = 'approved';
    const STATUS_NOT_APPROVED = 'not_approved';
    const STATUS_NOT_AVAILABLE = 'not_available';
    const STATUS_REPLACED = 'replaced';
    const STATUS_NOT_YET_FULFILLED = 'not_yet_fulfilled';

    protected $table = 'logistic_realization_items';

    protected $fillable = [
        'id',
        'agency_id',
        'applicant_id',
        'need_id',
        'material_group',
        'unit_id',
        'status',
        'realization_date',
        'created_by',
        'updated_by',
        'final_product_id',
        'final_product_name',
        'final_quantity',
        'final_unit',
        'final_date',
        'final_status',
        'final_unit_id',
        'final_by',
        'final_at'
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

    public function finalizedBy()
    {
        return $this->hasOne('App\User', 'id', 'final_by');
    }
}
