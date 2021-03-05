<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Outbound extends Model
{
    public function scopeReadyToDeliver($query)
    {
        return $this->where('status', 'plan');
    }
}
