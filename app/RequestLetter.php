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

    static function getForPrint($id)
    {
        $requestLetter = self::select(
            'request_letters.id',
            'request_letters.outgoing_letter_id',
            'request_letters.applicant_id',
            'applicants.application_letter_number',
            'applicants.agency_id',
            'applicants.created_at',
            'agency.agency_name',
            'agency.location_district_code',
            'districtcities.kemendagri_kabupaten_nama',
            'applicants.applicant_name'
        )
        ->join('applicants', 'applicants.id', '=', 'request_letters.applicant_id')
        ->join('agency', 'agency.id', '=', 'applicants.agency_id')
        ->join('districtcities', 'districtcities.kemendagri_kabupaten_kode', '=', 'agency.location_district_code')
        ->where('request_letters.outgoing_letter_id', $id)
        ->orderBy('request_letters.id')->get();

        return $requestLetter;
    }
}
