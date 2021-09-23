<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationRequest extends Model
{
    protected $appends = ['allocation_material_requests_total'];

    public function getAllocationMaterialRequestsTotalAttribute($value)
    {
        $totalQtyMaterialRequested = AllocationMaterialRequest::where('allocation_request_id', $this->id)->sum('qty');
        return $totalQtyMaterialRequested;
    }

    public function allocationDistributionRequests()
    {
        return $this->hasMany('App\AllocationDistributionRequest');
    }

    public function allocationMaterials()
    {
        return $this->hasMany('App\AllocationMaterial');
    }

    public function allocationMaterialRequests()
    {
        return $this->hasMany('App\AllocationMaterialRequest');
    }
}
