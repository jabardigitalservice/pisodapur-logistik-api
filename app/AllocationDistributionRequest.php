<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationDistributionRequest extends Model
{
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
