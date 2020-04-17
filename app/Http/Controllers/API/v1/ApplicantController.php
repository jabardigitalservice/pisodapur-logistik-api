<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Applicant;
use App\FileUpload;
use Illuminate\Support\Facades\Storage;

class ApplicantController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agency_id' => 'required',
            'applicant_name' => 'required|string',
            'applicants_office' => 'required|string',
            'file' => 'mimes:jpeg,jpg,png,pdf|max:10240',
            'email' => 'required|email',
            'primary_phone_number' => 'required|numeric',
            'secondary_phone_number' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        } else {

            if ($request->has('file')) {
                $path = Storage::disk('s3')->put('registration/applicant_identity', $request->file);
            }

            $fileUpload = FileUpload::create(['name' => $path]);

            $model = new Applicant();
            $model->fill($request->input());
            $model->file = $fileUpload->id;
            if ($model->save()) {
                $model->file = Storage::disk('s3')->url($fileUpload->name);
                return ($model);
            }
        }
    }
}
