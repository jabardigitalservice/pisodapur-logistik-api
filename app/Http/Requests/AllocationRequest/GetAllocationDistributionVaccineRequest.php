<?php

namespace App\Http\Requests\AllocationRequest;

use Illuminate\Foundation\Http\FormRequest;

class GetAllocationDistributionVaccineRequest extends FormRequest
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
            'allocation_request_id' => 'required|numeric|exists:allocation_requests,id',
            'limit' => 'numeric',
            'page' => 'numeric'
        ];
    }
}
