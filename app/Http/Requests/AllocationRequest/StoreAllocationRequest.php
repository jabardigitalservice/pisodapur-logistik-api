<?php

namespace App\Http\Requests\AllocationRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreAllocationRequest extends FormRequest
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
            'letter_number' => 'required|unique:allocation_requests,letter_number',
            'letter_date' => 'required||date_format:Y-m-d',
            'applicant_name' => 'required',
            'applicant_position' => 'required',
            'applicant_agency_id' => 'required|exists:master_faskes,id',
            'applicant_agency_name' => 'required',
            'letter_url' => 'required',
            'instance_list' => 'required'
        ];
    }
}
