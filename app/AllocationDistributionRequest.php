<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationDistributionRequest extends Model
{
    const STORE_RULE = [
        'agency_id' => 'required|exists:master_faskes,id',
        'agency_name' => 'required',
        'distribution_plan_date' => 'required|date_format:Y-m-d',
        'allocation_material_requests' => 'required'
    ];

    protected $fillable = [
        'allocation_request_id',
        'agency_id',
        'agency_name',
        'distribution_plan_date'
    ];

    public function allocationMaterialRequests()
    {
        return $this->hasMany('App\AllocationMaterialRequest');
    }

    public function scopeFilter($query, $request)
    {
        $query->when($request->has('search'), function ($query) use ($request) {
            $query->where('agency_name', 'LIKE', '%' . $request->input('search') . '%');
        });

        return $query;
    }
}
