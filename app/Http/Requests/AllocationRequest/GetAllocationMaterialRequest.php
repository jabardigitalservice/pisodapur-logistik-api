<?php

namespace App\Http\Requests\AllocationRequest;

use Illuminate\Foundation\Http\FormRequest;

class GetAllocationMaterialRequest extends FormRequest
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
            'limit' => 'nullable|numeric',
            'page' => 'nullable|numeric',
            'is_paginated' => 'boolean',
            'matg_id' => 'nullable|exists:products,material_group',
        ];
    }
}
