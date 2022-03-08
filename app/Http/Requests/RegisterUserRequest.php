<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Enum\Laravel\Rules\EnumRule;
use App\Enums\UserAppEnum;

class RegisterUserRequest extends FormRequest
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
            'name' => 'required',
            'username' => 'required | unique:users',
            'email' => 'required | email | unique:users',
            'password' => 'required',
            'roles' => 'required',
            'agency_name' => 'required',
            'code_district_city' => 'required',
            'name_district_city' => 'required',
            'phase' => 'required',
            'app' => [new EnumRule(UserAppEnum::class)]
        ];
    }
}
