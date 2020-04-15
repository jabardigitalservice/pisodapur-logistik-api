<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Agency;
use Validator;

class AgencyController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agency_type' => 'required|string',
            'agency_name' => 'required',
            'phone_number' => 'required|numeric',
            'location_district_code' => 'required|string',
            'location_subdistrict_code' => 'required|string',
            'location_village_code' => 'required|string',
            'location_address' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        } else {
            $model = new Agency();
            $model->fill($request->input());
            if ($model->save()) {
                return $model;
            }
        }
    }
}
