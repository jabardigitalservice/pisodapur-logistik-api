<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Applicant;
use Illuminate\Http\Request;

class IncomingLetter extends Model
{
    static function getIncomingLetterList(Request $request)
    {
        $data = []; 
        $limit = $request->input('limit', 10);
        $sort = $request->filled('sort') ? ['applicants.application_letter_number ' . $request->input('sort') ] : ['applicants.created_at ASC'];

        $data = Applicant::select(
            'applicants.id',
            'applicants.application_letter_number as letter_number',
            'applicants.agency_id',
            'agency.agency_name',
            'agency.agency_type',
            'agency.location_district_code as district_code',
            'districtcities.kemendagri_kabupaten_nama as district_name',
            'applicants.applicant_name',
            'applicants.created_at as letter_date',
            'request_letters.id as incoming_mail_status',
            'request_letters.id as request_letters_id'
        )
        ->where(function ($query) use ($request) {
            if ($request->filled('letter_date')) {
                $query->whereRaw("DATE(applicants.created_at) = '" . $request->input('letter_date') . "'");
            }
            if ($request->filled('district_code')) {
                $query->where('agency.location_district_code', '=', $request->input('district_code'));
            }
            if ($request->filled('agency_type')) {
                $query->where('agency.agency_type', '=', $request->input('agency_type'));
            }
            if ($request->filled('letter_number')) {
                $query->where('applicants.application_letter_number', 'LIKE', "%{$request->input('letter_number')}%");
            }
            if ($request->filled('mail_status')) {
                if ($request->input('mail_status') === 'exists') {
                    $query->whereNotNull('request_letters.id');
                } else {
                    $query->whereNull('request_letters.id');
                }
            }
        })
        ->join('agency', 'agency.id', '=', 'applicants.agency_id')
        ->join('districtcities', 'districtcities.kemendagri_kabupaten_kode', '=', 'agency.location_district_code')
        ->leftJoin('request_letters', 'request_letters.applicant_id', '=', 'applicants.id')
        ->where('applicants.is_deleted', '!=', 1)
        ->whereNotNull('applicants.finalized_by')
        ->orderByRaw(implode($sort))->paginate($limit);

        $data->getCollection()->transform(function ($applicant, $key) {
            $applicant->letter_date = date('Y-m-d', strtotime($applicant->letter_date));
            return $applicant;
        });

        return $data;
    }

    static function showIncomingLetterDetail(Request $request, $id)
    {
        $data = Applicant::select(
            'applicants.id',
            'applicants.application_letter_number as letter_number',
            'applicants.agency_id',   
            'applicants.applicants_office', 
            'applicants.file', 
            'applicants.email', 
            'applicants.primary_phone_number', 
            'applicants.secondary_phone_number', 
            'applicants.verification_status', 
            'applicants.note', 
            'applicants.approval_status', 
            'applicants.approval_note', 
            'applicants.stock_checking_status',  
            'applicants.created_at',  
            'applicants.updated_at',  
            'agency.location_district_code',   
            'agency.location_subdistrict_code',   
            'agency.location_village_code',   
            'agency.agency_type',   
            'applicants.applicant_name',
            'applicants.created_at as letter_date',
            'request_letters.id as incoming_mail_status',
            'request_letters.id as request_letters_id'
            )
            ->with([
                'masterFaskesType' => function ($query) {
                    return $query->select(['id', 'name']);
                },
                'agency' => function ($query) {
                    return $query;
                },
                'letter' => function ($query) {
                    return $query->select(['id', 'agency_id', 'letter']);
                },
                'city' => function ($query) {
                    return $query->select(['id', 'kemendagri_provinsi_nama', 'kemendagri_kabupaten_kode', 'kemendagri_kabupaten_nama']);
                },
                'subDistrict' => function ($query) {
                    return $query->select(['id', 'kemendagri_kecamatan_kode', 'kemendagri_kecamatan_nama']);
                },
                'village' => function ($query) {
                    return $query->select(['id', 'kemendagri_desa_kode', 'kemendagri_desa_nama']);
                }
            ])
            ->join('agency', 'agency.id', '=', 'applicants.agency_id')
            ->findOrFail($id);
            $data->letter_date = date('Y-m-d', strtotime($data->letter_date));

        return $data;
    }
}
