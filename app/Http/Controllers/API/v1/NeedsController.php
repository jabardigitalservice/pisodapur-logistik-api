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
        foreach ($request->logistic_request as $key => $value) {
            $validator = Validator::make($value, [
                'agency_id' => 'required|numeric',
                'applicant_id' => 'required|numeric',
                'item' => 'string',
                'brand' => 'string',
                'quantity' => 'numeric',
                'unit' => 'string',
                'usage' => 'string',
                'priority' => 'string'
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
            } else {
                Needs::create([
                'agency_id' => $value['agency_id'],
                'applicant_id' => $value['applicant_id'],
                'item' => $value['item'],
                'brand' => $value['brand'],
                'quantity' => $value['quantity'],
                'unit' => $value['unit'],
                'usage' => $value['usage'],
                'priority' => $value['priority']
                ]);
            }
        }
    }
}
