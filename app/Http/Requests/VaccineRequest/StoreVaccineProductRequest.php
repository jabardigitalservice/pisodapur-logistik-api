<?php

namespace App\Http\Requests\VaccineRequest;

use App\Enums\Vaccine\VaccineProductCategoryEnum;
use App\Enums\VaccineProductRequestStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Enum\Laravel\Rules\EnumRule;

class StoreVaccineProductRequest extends FormRequest
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
            'vaccine_request_id' => 'required|exists:vaccine_requests,id',
            'category' => ['required', new EnumRule(VaccineProductCategoryEnum::class)],
            'recommendation_product_id' => 'required|exists:allocation_materials,material_id',
            'recommendation_date' => 'required|date',
            'recommendation_product_name' => 'required|exists:allocation_materials,material_name',
            'recommendation_quantity' => 'required|numeric',
            'recommendation_UoM' => 'required',
            'recommendation_status' => [
                'required',
                Rule::in([VaccineProductRequestStatusEnum::urgent(), VaccineProductRequestStatusEnum::other()])
            ],
            'recommendation_reason' => 'nullable',
            'recommendation_file' => 'required|mimes:jpeg,jpg,png,pdf|max:10240',
            'description' => 'nullable',
            'usage' => 'required',
        ];
    }
}
