<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationMaterialRequest extends Model
{
    protected $fillable = [
        'allocation_request_id',
        'allocation_distribution_request_id',
        'matg_id',
        'material_id',
        'material_name',
        'qty',
        'UoM'
    ];
}
