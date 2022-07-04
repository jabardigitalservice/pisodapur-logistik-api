<?php

namespace App\Models\Vaccine;

class VaccineTracking extends VaccineRequest
{
    protected $table = 'vaccine_requests';

    public function scopeTracking($query, $request)
    {
        if (str_contains($request->search, '@')) {
            $query->where('applicant_email', $request->search);
        } else {
            $query->where(function($query) use ($request) {
                $query->where('id', $request->search)
                    ->orWhere('applicant_primary_phone_number', $request->search);
                    // ->orWhere('applicant_email', $request->search)
                    // ->orWhere('applicant_primary_phone_number', $request->search);
            });
        }
        return $query;
    }
}
