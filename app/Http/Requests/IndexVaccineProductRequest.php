<?php

namespace App\Http\Requests;

use App\Enums\Vaccine\VaccineProductCategoryEnum;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;

class IndexVaccineProductRequest extends FormRequest
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
            'category' => [new EnumRule(VaccineProductCategoryEnum::class)]
        ];
    }
}
