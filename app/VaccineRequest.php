<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VaccineRequest extends Model
{
    protected $fillable = [
        'agency_id',
        'agency_type_id',
        'agency_name',
        'agency_phone_number',
        'agency_location_district_code',
        'agency_location_subdistrict_code',
        'agency_location_village_code',
        'agency_location_address',
        'applicant_fullname',
        'applicant_position',
        'applicant_email',
        'applicant_primary_phone_number',
        'applicant_secondary_phone_number',
        'letter_number',
        'letter_file_url',
        'applicant_file_url',
    ];

    public function getLetterFileUrlAttribute($value)
    {
        return config('aws.url') . $value;
    }

    public function getApplicantFileUrlAttribute($value)
    {
        return config('aws.url') . $value;
    }

    static function add($request)
    {
        $vaccineRequest = [
            'agency_id' => $request->input('master_faskes_id'),
            'agency_type_id' => $request->input('agency_type'),
            'agency_name' => $request->input('agency_name'),
            'agency_phone_number' => $request->input('phone_number'),
            'agency_location_district_code' => $request->input('location_district_code'),
            'agency_location_subdistrict_code' => $request->input('location_subdistrict_code'),
            'agency_location_village_code' => $request->input('location_village_code'),
            'agency_location_address' => $request->input('location_address'),
            'applicant_fullname' => $request->input('applicant_name'),
            'applicant_position' => $request->input('applicants_office'),
            'applicant_email' => $request->input('email'),
            'applicant_primary_phone_number' => $request->input('primary_phone_number'),
            'applicant_secondary_phone_number' => $request->input('secondary_phone_number'),
            'letter_number' => $request->input('application_letter_number'),
            'letter_file_url' => $request->input('letter_file_url'),
            'applicant_file_url' => $request->input('applicant_file_url'),
        ];
        return VaccineRequest::create($vaccineRequest);
    }
}
