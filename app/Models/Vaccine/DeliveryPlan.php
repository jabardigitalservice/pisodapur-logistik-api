<?php

namespace App\Models\Vaccine;

class DeliveryPlan extends VaccineRequest
{
    protected $table = 'vaccine_requests';

    public function scopeDeliveryPlan($query)
    {
        return $query
            ->whereNotNull('verified_at')
            ->whereNotNull('approved_at')
            ->whereNotNull('finalized_at')
            ->whereNull('integrated_at')
            ->whereNull('delivered_at');
    }
}
