<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationDistributionRequest extends Model
{
    public function allocationMaterialRequests()
    {
        return $this->hasMany('App\AllocationMaterialRequest');
    }
}
