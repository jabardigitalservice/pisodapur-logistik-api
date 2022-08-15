<?php

namespace App\Models\Vaccine;

use Illuminate\Database\Eloquent\Model;

class VaccineRequestRating extends Model
{
    protected $fillable = [
        'vaccine_request_id', 'phase', 'score', 'created_by'
    ];
}
