<?php

namespace App\Http\Requests;

use App\Enums\LogisticRatingEnum;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;

class StoreLogisticRatingRequest extends FormRequest
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
            'agency_id' => ['required', 'integer', 'exists:agency,id'],
            'phase' => ['required', new EnumRule(LogisticRatingEnum::class)],
            'score' => ['required', 'integer', 'between:1,5'],
        ];
    }
}
