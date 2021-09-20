<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationRequest extends Model
{
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
