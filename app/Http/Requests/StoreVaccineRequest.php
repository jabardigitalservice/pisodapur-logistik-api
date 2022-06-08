<?php

namespace App\Http\Requests;

use App\Enums\Vaccine\VaccineProductCategoryEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreVaccineRequest extends FormRequest
{
    const RULES = [
        'master_faskes_id' => 'nullable|numeric',
        'agency_type' => 'required|numeric',
        'agency_name' => 'required|string',
        'location_district_code' => 'required|string|exists:districtcities,kemendagri_kabupaten_kode',
        'location_subdistrict_code' => 'required|string|exists:subdistricts,kemendagri_kecamatan_kode',
        'location_village_code' => 'required|string|exists:villages,kemendagri_desa_kode',
        'applicant_name' => 'required|string',
        'primary_phone_number' => 'required|numeric',
        'letter_file' => 'required|file|max:10240',
        'applicant_file' => 'mimes:jpeg,jpg,png,pdf|max:10240',
        'is_letter_file_final' => 'required|boolean',
        'application_letter_number' => 'required|string',
    ];

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
        $rules = self::RULES;
        $rules += [
            'logistic_request' => [
                'required',
                'json',
                function ($attribute, $value, $fail) {
                    foreach (json_decode($value, true) as $key => $param) {
                        $result = StoreVaccineRequest::logisticRequestRules($param);
                        if (!$result['valid']) {
                            $fail($result['fails']);
                        }
                    }
                },
            ],
        ];

        return $rules;
    }

    static public function logisticRequestRules($param)
    {
        $fails = '';
        $valid = true;
        if (!is_numeric($param['product_id'])) {
            $fails .= 'logistic_request.product_id is required. ';
            $valid = false;
        }

        if (!is_numeric($param['quantity'])) {
            $fails .= 'logistic_request.quantity is must be numeric. ';
            $valid = false;
        }

        if (!$param['unit']) {
            $fails .= 'logistic_request.unit is required. ';
            $valid = false;
        }

        if (!in_array($param['category'], [VaccineProductCategoryEnum::vaccine(), VaccineProductCategoryEnum::vaccine_support()])) {
            $fails .= 'logistic_request.category is invalid. ';
            $valid = false;
        }

        return [
            'valid' => $valid,
            'fails' => $fails,
        ];
    }
}
