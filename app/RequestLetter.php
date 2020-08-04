<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestLetter extends Model
{
    use SoftDeletes;
    protected $table = 'request_letters';
    
    protected $fillable = [
        'outgoing_letter_id',
        'applicant_id'
    ];

    public function outgoingLetter()
    {
        return $this->belongsToMany('App\OutgoingLetter', 'id', 'outgoing_letter_id');
    }
}
