<?php

namespace App\Http\Requests\LogisticRealizationItem;

use App\LogisticRealizationItems;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
     *
     *
     */
    public function rules()
    {
        $params = [
            'agency_id' => 'required|numeric',
            'applicant_id' => 'required|numeric',
            'need_id' => 'required|numeric',
            'product_id' => 'nullable|string',
            'status' => ['required', Rule::in(LogisticRealizationItems::STATUS)],
            'store_type' => 'required|string',
            'product_name' => 'nullable',
        ];

        if (!in_array($this->status, [LogisticRealizationItems::STATUS_NOT_AVAILABLE, LogisticRealizationItems::STATUS_NOT_YET_FULFILLED])) {
            $params += [
                'recommendation_quantity' => 'nullable|numeric',
                'recommendation_date' => 'nullable|date',
                'recommendation_unit' => 'nullable|string',
                'recommendation_unit_id' => 'nullable|string',
                'realization_quantity' => 'nullable|numeric',
                'realization_date' => 'nullable|date',
                'realization_unit' => 'nullable|string',
                'realization_unit_id' => 'nullable|string',
                'material_group' => 'nullable|nullable',
            ];
        }
        return $params;
    }
}
