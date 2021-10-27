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
        'agency_city_id',
        'agency_district_id',
        'agency_village_id',
        'agency_address',
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
        $awsUrl = config('aws.url');
        return $value ? $awsUrl . $value : "";
    }

    public function getApplicantFileUrlAttribute($value)
    {
        $awsUrl = config('aws.url');
        return $value ? $awsUrl . $value : "";
    }

    static function add($request)
    {
        $vaccineRequest = [
            'agency_id' => $request->input('master_faskes_id'),
            'agency_type_id' => $request->input('agency_type'),
            'agency_name' => $request->input('agency_name'),
            'agency_phone_number' => $request->input('phone_number'),
            'agency_city_id' => $request->input('location_district_code'),
            'agency_district_id' => $request->input('location_subdistrict_code'),
            'agency_village_id' => $request->input('location_village_code'),
            'agency_address' => $request->input('location_address'),
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

    public function scopeFilter($query, $request)
    {
        $query->when($request->has('search'), function ($query) use ($request) {
            $query->where('agency_name', 'LIKE', '%' . $request->input('search') . '%');
        })
        ->when($request->has('start_date') && $request->has('end_date'), function ($query) use ($request) {
            $query->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date')]);
        })
        ->when($request->has('city_id'), function ($query) use ($request) {
            $query->where('agency_city_id', $request->input('city_id'));
        })
        ->when($request->has('is_completed'), function ($query) use ($request) {
            $query->where('is_completed', $request->input('is_completed'));
        })
        ->when($request->has('is_completed'), function ($query) use ($request) {
            $query->where('is_completed', $request->input('is_completed'));
        })
        ->when($request->has('is_urgency'), function ($query) use ($request) {
            $query->where('is_urgency', $request->input('is_urgency'));
        });

        $query->whereHas('masterFaskes', function ($query) use ($request) {
            $query->when($request->has('is_reference'), function ($query) use ($request) {
                $query->where('is_reference', $request->input('is_reference'));
            });
        });
        return $query;
    }

    public function scopeSort($query, $request)
    {
        $query->when($request->has('sort'), function ($query) use ($request) {
            $query->orderBy('agency_name', $request->input('sort'));
        });
        return $query;
    }

    public function vaccineProductRequests()
    {
        return $this->hasMany('App\VaccineProductRequest');
    }

    public function masterFaskesType()
    {
        return $this->hasOne('App\MasterFaskesType', 'id', 'agency_type_id');
    }

    public function masterFaskes()
    {
        return $this->hasOne('App\MasterFaskes', 'id', 'agency_id');
    }

    public function village()
    {
        return $this->hasOne('App\Village', 'kemendagri_desa_kode', 'agency_village_id');
    }
}
