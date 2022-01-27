<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVaccineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'master_faskes_id' => 'required|numeric|exists:master_faskes,id',
            'agency_type' => 'required|numeric|exists:master_faskes_types,id',
            'agency_name' => 'required|string',
            'location_district_code' => 'required|string|exists:districtcities,kemendagri_kabupaten_kode',
            'location_subdistrict_code' => 'required|string|exists:subdistricts,kemendagri_kecamatan_kode',
            'location_village_code' => 'required|string|exists:villages,kemendagri_desa_kode',
            'applicant_name' => 'required|string',
            'primary_phone_number' => 'required|numeric',
            'logistic_request' => 'required|json',
            'letter_file' => 'required|mimes:jpeg,jpg,png,pdf|max:10240',
            'applicant_file' => 'mimes:jpeg,jpg,png,pdf|max:10240',
            'application_letter_number' => 'required|string'
        ];
    }
}
