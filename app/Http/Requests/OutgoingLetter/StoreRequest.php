<?php

namespace App\Http\Requests\OutgoingLetter;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'letter_name' => 'required',
            'letter_date' => 'required',
            'letter_request' => 'required',
        ];
    }
}
