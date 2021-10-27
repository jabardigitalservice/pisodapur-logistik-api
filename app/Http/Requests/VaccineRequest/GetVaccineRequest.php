<?php

namespace App\Http\Requests\VaccineRequest;

use App\Enums\ApplicantStatusEnum;
use App\Enums\OrderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;

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
            'page' => 'numeric',
            'status' => [new EnumRule(ApplicantStatusEnum::class)],
            'sort' => [new EnumRule(OrderEnum::class)],
            'is_reference' => 'boolean',
            'is_completed' => 'boolean',
            'is_urgency' => 'boolean',
            'start_date' => 'required_with:end_date|date',
            'end_date' => 'required_with:start_date|date',
        ];
    }
}
