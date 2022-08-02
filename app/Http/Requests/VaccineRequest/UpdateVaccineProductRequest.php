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
        $rule = [
            'phase' => 'required',
            'finalized_product_id' => ['required_if:phase,finalized', 'exists:allocation_materials,material_id'],
            'finalized_date' => ['required_if:phase,finalized', 'date'],
            'finalized_product_name' => ['required_if:phase,finalized', 'exists:allocation_materials,material_name'],
            'finalized_quantity' => ['required_if:phase,finalized', 'numeric'],
            'finalized_UoM' => ['required_if:phase,finalized'],
            'finalized_status' => ['required_if:phase,finalized', new EnumRule(VaccineProductRequestStatusEnum::class)]
        ];

        if ($this->phase == 'recommendation') {
            $rule = [
                'phase' => 'required',
                'recommendation_product_id' => ['required_if:phase,recommendation', 'exists:allocation_materials,material_id'],
                'recommendation_date' => ['required_if:phase,recommendation', 'date'],
                'recommendation_product_name' => ['required_if:phase,recommendation', 'exists:allocation_materials,material_name'],
                'recommendation_quantity' => ['required_if:phase,recommendation', 'numeric'],
                'recommendation_UoM' => ['required_if:phase,recommendation'],
                'recommendation_status' => ['required_if:phase,recommendation', new EnumRule(VaccineProductRequestStatusEnum::class)],
                'recommendation_note' => 'nullable',
            ];
        }
        return $rule;
    }
}
