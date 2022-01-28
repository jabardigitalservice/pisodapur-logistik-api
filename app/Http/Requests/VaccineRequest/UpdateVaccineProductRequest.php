<?php

namespace App\Http\Requests\VaccineRequest;

use App\Enums\VaccineProductRequestStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;

class UpdateVaccineProductRequest extends FormRequest
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
            'recommendation_product_id' => 'nullable|exists:allocation_materials,material_id',
            'recommendation_date' => 'nullable|date',
            'recommendation_product_name' => 'nullable|exists:allocation_materials,material_name',
            'recommendation_quantity' => 'nullable|numeric',
            'recommendation_status' => ['nullable', new EnumRule(VaccineProductRequestStatusEnum::class)],
            'finalized_product_id' => 'nullable|exists:allocation_materials,material_id',
            'finalized_date' => 'nullable|date',
            'finalized_product_name' => 'nullable|exists:allocation_materials,material_name',
            'finalized_quantity' => 'nullable|numeric',
            'finalized_status' => ['nullable', new EnumRule(VaccineProductRequestStatusEnum::class)]
        ];
    }
}
