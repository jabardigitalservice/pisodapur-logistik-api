<?php

namespace App\Http\Requests\VaccineRequest;

use Illuminate\Foundation\Http\FormRequest;

class GetVaccineRequest extends FormRequest
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
            'limit' => 'numeric',
            'page' => 'numeric'
        ];
    }
}
