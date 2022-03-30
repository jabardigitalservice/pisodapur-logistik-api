<?php

namespace App\Models\Vaccine;

class DeliveryPlan extends VaccineRequest
{
    protected $table = 'vaccine_requests';

    public function scopeDeliveryPlan($query, $request)
    {
        $query->where('is_integrated', $request->input('is_integrated'));
        return $query;
    }
}
