<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutgoingLetter extends Model
{
    use SoftDeletes;
    protected $table = 'outgoing_letters';
    
    const STATUS = [
        'not_approved',
        'approved'
    ];
    
    protected $fillable = [
        'user_id',
        'letter_number',
        'letter_date',
        'status',
        'filename',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
