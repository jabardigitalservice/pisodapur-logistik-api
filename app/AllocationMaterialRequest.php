<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationMaterialRequest extends Model
{
    const STORE_RULE = [
        'matg_id' => 'required|exists:allocation_materials,matg_id',
        'material_id' => 'required|exists:allocation_materials,material_id',
        'material_name' => 'required',
        'qty' => 'required|numeric',
    ];

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
