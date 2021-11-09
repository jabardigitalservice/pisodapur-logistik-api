<?php

namespace App\Http\Requests\VaccineRequest;

use App\Enums\MasterFaskesVerificationStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;

class GetMasterFaskesRequest extends FormRequest
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
            'is_paginated' => 'boolean',
            'is_faskes' => 'boolean',
            'id_tipe_faskes' => 'nullable|exists:master_faskes_types,id',
            'verification_status' => [new EnumRule(MasterFaskesVerificationStatusEnum::class)],
        ];
    }
}
