<?php

namespace App\Http\Requests\VaccineRequest;

use App\Enums\ApplicantStatusEnum;
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
        ];
    }
}
