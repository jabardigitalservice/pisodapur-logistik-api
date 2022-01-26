<?php

namespace App\Http\Requests\VaccineRequest;

use App\Enums\VaccineRequestStatusEnum;
use App\Enums\OrderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;

class UpdateVaccineRequest extends FormRequest
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
            'note' => $this->noteCondition()
        ];
    }

    public function noteCondition()
    {
        $rule = ['nullable'];

        if (in_array($this->status, [VaccineRequestStatusEnum::approval_rejected(), VaccineRequestStatusEnum::verification_rejected()])) {
            $rule = ['required'];
        }
        return $rule;
    }
}
