<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogisticRealizationItems extends Model
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
        'product_id',
        'product_name',
        'realization_unit',
        'material_group',
        'realization_quantity',
        'unit_id',
        'status',
        'realization_date',
        'created_by',
        'updated_by',
        'recommendation_by',
        'recommendation_at',
        'final_product_id',
        'final_product_name',
        'final_quantity',
        'final_unit',
        'final_date',
        'final_status',
        'final_unit_id',
        'final_by',
        'final_at',
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

    public function verifiedBy()
    {
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function recommendBy()
    {
        return $this->hasOne('App\User', 'id', 'recommendation_by');
    }

    public function realizedBy()
    {
        return $this->hasOne('App\User', 'id', 'realization_by');
    }

    public function getFinalUnitAttribute($value)
    {
        return $value ? $value : 'PCS';
    }

    public function getQtyAttribute($value)
    {
        return number_format($value, 0, ",", ".");
    }

    static function withPICData($data)
    {
        return $data->with([
            'verifiedBy' => function ($query) {
                return $query->select(['id', 'name', 'agency_name', 'handphone']);
            },
            'recommendBy' => function ($query) {
                return $query->select(['id', 'name', 'agency_name', 'handphone']);
            },
            'realizedBy' => function ($query) {
                return $query->select(['id', 'name', 'agency_name', 'handphone']);
            }
        ]);
    }
}
