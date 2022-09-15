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

        //penambahan kondisi jika value `recommendation_status` atau `finalized_status` = `not_available`
        if (in_array(VaccineProductRequestStatusEnum::not_available(), [
            $this->recommendation_status, $this->finalized_status
        ])) {
            $rule['recommendation_product_id'] = ['nullable'];
            $rule['recommendation_product_name'] = ['nullable'];
            $rule['recommendation_UoM'] = ['nullable'];
            $rule['recommendation_quantity'] = ['nullable', 'numeric'];
            $rule['finalized_product_id'] = ['nullable'];
            $rule['finalized_product_name'] = ['nullable'];
            $rule['finalized_UoM'] = ['nullable'];
            $rule['finalized_quantity'] = ['nullable', 'numeric'];
            $rule['finalized_date'] = ['nullable', 'date'];
            $rule['finalized_status'] = [
                'nullable',
                new EnumRule(VaccineProductRequestStatusEnum::class)
            ];
        }

        return $rule;
    }
}
