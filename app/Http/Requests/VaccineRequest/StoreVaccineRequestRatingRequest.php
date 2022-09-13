<?php

namespace App\Http\Requests\VaccineRequest;

use App\Enums\Vaccine\VaccineRequestRatingEnum;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;

class StoreVaccineRequestRatingRequest extends FormRequest
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
            'vaccine_request_id' => ['required', 'integer', 'exists:vaccine_requests,id'],
            'phase' => ['required', new EnumRule(VaccineRequestRatingEnum::class)],
            'score' => ['required', 'integer', 'between:1,5'],
        ];
    }
}
