<?php

namespace App\Http\Requests\VaccineRequest;

use App\Enums\VaccineRequestStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;

class UpdateStatusVaccineRequest extends FormRequest
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
            'status' => ['required', new EnumRule(VaccineRequestStatusEnum::class)],
            'vaccine_status_note' => [
                'nullable',
                'array',
            ],
            'note' => 'nullable'
        ];
    }
}
