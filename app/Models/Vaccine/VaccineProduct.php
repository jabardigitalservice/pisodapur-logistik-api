<?php

namespace App\Models\Vaccine;

use Illuminate\Database\Eloquent\Model;

class VaccineProduct extends Model
{
    public function getUnitAttribute($value)
    {
        return json_decode($value);
    }

    public function getPurposesAttribute($value)
    {
        return json_decode($value);
    }
}
