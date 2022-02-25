<?php

namespace App\Http\Requests;

use App\Enums\Vaccine\VaccineProductCategoryEnum;
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
        return true;
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
            'logistic_request' => [
                'required',
                'json',
                function ($attribute, $value, $fail) {
                    foreach (json_decode($value, true) as $key => $param) {
                        if (!is_numeric($param['product_id'])) {
                        }

                        if (!is_numeric($param['quantity'])) {
                            $fail('logistic_request.quantity is must be numeric.');
                        }

                        if (!$param['unit']) {
                            $fail('logistic_request.unit is required.');
                        }

                        if (!in_array($param['category'], [VaccineProductCategoryEnum::vaccine(), VaccineProductCategoryEnum::vaccine_support()])) {
                            $fail('logistic_request.category is invalid.');
                        }
                    }
                },
            ],
            'letter_file' => 'required|mimes:jpeg,jpg,png,pdf|max:10240',
            'applicant_file' => 'mimes:jpeg,jpg,png,pdf|max:10240',
            'is_letter_file_final' => 'required|boolean',
            'application_letter_number' => 'required|string'
        ];
    }
}
