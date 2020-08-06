<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutgoingLetter extends Model
{
    use SoftDeletes;
    protected $table = 'outgoing_letters';

    const APPROVED = 'approved';
    const NOT_APPROVED = 'not_approved';
    
    protected $fillable = [
        'user_id',
        'letter_number',
        'letter_date',
        'status',
        'filename'
    ];

    /**
     * Get total request letter by Outgoing Letter ID
     */
    public function requestLetter()
    {
        return $this->hasMany('App\RequestLetter', 'outgoing_letter_id', 'id');
    }

    /**
     * Function to return Request Letter Total
     *
     * @param [int] $value
     * @return string / null
     */
    public function getRequestLetterTotalAttribute()
    {
        return RequestLetter::where('outgoing_letter_id', $this->id)
        ->join('applicants', 'applicants.id', '=', 'request_letters.applicant_id')
        ->where('applicants.verification_status', '=', Applicant::STATUS_VERIFIED)
        ->count();
    }
}
