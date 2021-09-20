<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationRequest extends Model
{
    public function allocationDistributionRequest()
    {
        return $this->hasMany('App\AllocationDistributionRequest');
    }

    public function allocationMaterial()
    {
        return $this->hasMany('App\AllocationMaterial');
    }

    public function allocationMaterialRequest()
    {
        return $this->hasMany('App\AllocationMaterialRequest');
    }
}
