<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogisticRating extends Model
{
    protected $fillable = [
        'agency_id', 'phase', 'score', 'created_by'
    ];
}
