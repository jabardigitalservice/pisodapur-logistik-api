<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Needs;

class NeedsController extends Controller
{
    public function store(Request $request)
    {
        dd($request);

        // $validator = Validator::make($request->all(), [
        //     'agency_id' => 'required|numeric',
        //     'applicant_id' => 'required|numeric',
        //     'item' => 'string',
        //     'brand' => 'string',
        //     'quantity' => 'numeric',
        //     'unit' => 'string',
        //     'usage' => 'string',
        //     'priority' => 'string'
        // ]);
        // if ($validator->fails()) {
        //     return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        // } else {
        //     // $model = new Needs();
        //     // $model->fill($request->input());
        //     // if ($model->save()) {
        //     //     return $model;
        //     // }
        // }
    }
}
