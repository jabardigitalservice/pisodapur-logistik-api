<?php

namespace App\Http\Requests\VaccineRequest;

use App\Enums\OrderEnum;
use App\Enums\VaccineRequestStatusEnum;
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
            'limit' => 'nullable|numeric',
            'page' => 'nullable|numeric',
            'status' => ['nullable', new EnumRule(VaccineRequestStatusEnum::class)],
            'sort' => ['nullable', new EnumRule(OrderEnum::class)],
            'is_reference' => 'boolean',
            'is_completed' => 'boolean',
            'is_urgency' => 'boolean',
            'start_date' => 'nullable|required_with:end_date|date',
            'end_date' => 'nullable|required_with:start_date|date',
            'city_id' => 'nullable|exists:districtcities,kemendagri_kabupaten_kode',
            'faskes_type' => 'nullable|exists:master_faskes_types,id',
            'is_letter_file_final' => 'nullable|boolean',
        ];
    }
}
