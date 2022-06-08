<?php

namespace App\Http\Requests\LogisticRealizationItem;

use App\LogisticRealizationItems;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddRequest extends FormRequest
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
            'applicant_id' => 'nullable',
            'product_id' => 'required|string',
            'status' => ['required', Rule::in(LogisticRealizationItems::STATUS)],
            'store_type' => 'required|string',
            'product_name' => 'nullable',
        ];

        if (!in_array($this->status, [LogisticRealizationItems::STATUS_NOT_AVAILABLE, LogisticRealizationItems::STATUS_NOT_YET_FULFILLED])) {
            $params += [
                'recommendation_quantity' => 'required_if:store_type,recommendation',
                'recommendation_date' => 'required_if:store_type,recommendation|date',
                'recommendation_unit' => 'required_if:store_type,recommendation|string',
                'realization_quantity' => 'required_if:store_type,realization',
                'realization_date' => 'required_if:store_type,realization|date',
                'realization_unit' => 'required_if:store_type,realization|string',
                'material_group' => 'exclude_if:store_type,realization|nullable',
            ];
        }
        return $params;
    }
}
