<?php

namespace App\Models\Vaccine;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VaccineRequestStatusNote extends Model
{
    use SoftDeletes;
    protected $fillable = ['vaccine_request_id', 'status', 'vaccine_status_note_id', 'vaccine_status_note_nama'];
}
