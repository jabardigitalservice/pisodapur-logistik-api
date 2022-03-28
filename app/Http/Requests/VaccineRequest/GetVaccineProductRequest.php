<?php

namespace App\Http\Requests\VaccineRequest;

use App\Enums\Vaccine\VaccineProductCategoryEnum;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;

class GetVaccineProductRequest extends FormRequest
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
            'vaccine_request_id' => 'required|exists:vaccine_requests,id',
            'category' => [new EnumRule(VaccineProductCategoryEnum::class)],
        ];
    }
}
