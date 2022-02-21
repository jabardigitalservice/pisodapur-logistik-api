<?php

namespace App\Models\Vaccine;

use Illuminate\Database\Eloquent\Model;

class VaccineProduct extends Model
{
    public const CATEGORY_VACCINE = 'vaccine';
    public const CATEGORY_VACCINE_SUPPORT = 'vaccine_support';

    public function getUnitAttribute($value)
    {
        return json_decode($value);
    }
}
