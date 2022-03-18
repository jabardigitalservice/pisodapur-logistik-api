<?php

namespace App\Models\Vaccine;

use App\Enums\VaccineRequestStatusEnum;
use Illuminate\Database\Eloquent\Model;

class VaccineRequest extends Model
{
    protected $with = [
        'medicalFacility:id,name',
        'medicalFacilityType:id,name',
        'village',
        'verifiedBy:id,name',
        'approvedBy:id,name',
        'finalizedBy:id,name'
    ];

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
        'is_letter_file_final',
        'is_completed',
        'is_urgency',
        'status',
        'note'
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
        $user = auth()->user();
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
            'is_letter_file_final' => $request->input('is_letter_file_final'),
            'applicant_file_url' => $request->input('applicant_file_url'),
            'is_completed' => VaccineRequest::setIsCompleted($request),
            'created_by' => $user->id ?? null
        ];
        return VaccineRequest::create($vaccineRequest);
    }

    public static function setIsCompleted($request)
    {
        $isCompleted = 0;
        if ($request->input('applicant_file_url')
            && $request->input('primary_phone_number')
            && $request->input('email')
            && $request->input('location_address')
        ) {
            $isCompleted = 1;
        }

        return $isCompleted;
    }

    public function scopeFilter($query, $request)
    {
        return $query
                    ->when($request->input('status'), function ($query) use ($request) {
                        $query->where('status', $request->input('status'));
                    })
                    ->whenHasDate($request)
                    ->when($request->input('city_id'), function ($query) use ($request) {
                        $query->where('agency_city_id', $request->input('city_id'));
                    })
                    ->when($request->has('is_completed'), function ($query) use ($request) {
                        $query->where('is_completed', $request->input('is_completed'));
                    })
                    ->when($request->has('is_urgency'), function ($query) use ($request) {
                        $query->where('is_urgency', $request->input('is_urgency'));
                    })
                    ->when($request->input('faskes_type'), function ($query) use ($request) {
                        $query->where('agency_type_id', $request->input('faskes_type'));
                    })
                    ->isLetterFileFinal($request)
                    ->whereHasMedicalFacility($request);
    }

    public function scopeWhenHasDate($query, $request)
    {
        $query->when($request->input('start_date') && $request->input('end_date'), function ($query) use ($request) {
            $start_date = $request->input('start_date') . ' 00:00:00';
            $end_date = $request->input('end_date') . ' 23:59:59';
            $query->whereBetween('created_at', [$start_date, $end_date]);
        });
        return $query;
    }

    public function scopeIsLetterFileFinal($query, $request)
    {
        $query->when($request->has('is_letter_file_final'), function ($query) use ($request) {
            $query->where('is_letter_file_final', $request->input('is_letter_file_final'));
        });
        return $query;
    }

    public function scopeWhereHasMedicalFacility($query, $request)
    {
        $query->whereHas('medicalFacility', function ($query) use ($request) {
            $query
                ->when($request->input('search'), function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->input('search') . '%');
                });
        });
        return $query;
    }

    public function scopeSort($query, $request)
    {
        $query->when($request->input('sort'), function ($query) use ($request) {
            $query->orderBy('agency_name', $request->input('sort'));
        });
        return $query;
    }

    public function vaccineProductRequests()
    {
        return $this->hasMany('App\VaccineProductRequest');
    }

    public function medicalFacilityType()
    {
        return $this->hasOne('App\Models\MedicalFacilityType', 'id', 'agency_type_id');
    }

    public function medicalFacility()
    {
        return $this->hasOne('App\Models\MedicalFacility', 'id', 'agency_id');
    }

    public function village()
    {
        return $this->hasOne('App\Village', 'kemendagri_desa_kode', 'agency_village_id');
    }

    public function verifiedBy()
    {
        return $this->hasOne('App\User', 'id', 'verified_by');
    }

    public function approvedBy()
    {
        return $this->hasOne('App\User', 'id', 'approved_by');
    }

    public function finalizedBy()
    {
        return $this->hasOne('App\User', 'id', 'finalized_by');
    }

    public function outbounds()
    {
        return $this->hasMany('App\Outbound', 'req_id', 'id');
    }
}
