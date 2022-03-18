<?php

namespace App\Models\Vaccine;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VaccineRequestStatusNote extends Model
{
    use SoftDeletes;

    protected $with = ['vaccineStatusNote:id,name'];

    protected $fillable = ['vaccine_request_id', 'status', 'vaccine_status_note_id', 'note'];

    public function vaccineStatusNote()
    {
        return $this->hasOne('App\Models\Vaccine\VaccineStatusNote', 'id', 'vaccine_status_note_id');
    }
}
