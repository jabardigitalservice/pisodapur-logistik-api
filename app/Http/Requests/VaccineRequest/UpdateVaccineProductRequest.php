<?php

namespace App\Http\Requests\VaccineRequest;

use Illuminate\Foundation\Http\FormRequest;

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
            'recommendation_product_id' => 'nullable|exists:allocation_materials,material_id'
        ];
    }

    /**
    * Custom message for validation
    *
    * @return array
    */
   public function messages()
   {
       return [
           'recommendation_product_id.exists' => 'Recommendation Product not exists'
       ];
   }
}
