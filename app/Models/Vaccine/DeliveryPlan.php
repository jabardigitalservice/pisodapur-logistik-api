<?php

namespace App\Models\Vaccine;

class DeliveryPlan extends VaccineRequest
{
    protected $table = 'vaccine_requests';

    public function scopeDeliveryPlan($query, $request)
    {
        if ($request->input('is_integrated') == 1) {
            $query->where('status', 'integrated');
        } else {
            $query->where('status', 'finalized');
        }
        return $query;
    }
}
