<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\LogisticRealizationItems;
use Validator;

class LogisticRealizationItemController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'need_id' => 'numeric',
            'quantity' => 'numeric',
            'unit_id' => 'numeric',
            'realization_date' => 'date',
            'status' => 'string',
            'created_by' => 'string',
            'updated_by' => 'string'
        ]);
        if ($validator->fails()) {
            return response()->format(422,  $validator->messages()->all());
        } elseif (!in_array($request->status, LogisticRealizationItems::STATUS)) {
            return response()->json(['status' => 'fail', 'message' => 'verification_status_value_is_not_accepted']);
        } else {
            $model = new LogisticRealizationItems();
            $findOne = LogisticRealizationItems::where('need_id', $request->need_id)->orderBy('created_at', 'desc')->first();
            $model->fill($request->input());
            if ($model->save()) {            
                if($findOne){                
                    //updating latest log realization record 
                    $findOne->realization_ref_id = $model->id;
                    $findOne->deleted_at = date('Y-m-d H:i:s');
                    if ($findOne->save()) {
                        return response()->format(200, 'success', $model);
                    }
                } else {
                    return response()->format(200, 'success', $model);
                }
            }
        }
    }
}
