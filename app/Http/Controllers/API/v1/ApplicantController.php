<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Applicant;
use App\Fileupload;
use Illuminate\Support\Facades\Storage;

class ApplicantController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agency_id' => 'required',
            'applicant_name' => 'required|string',
            'applicants_office' => 'required|string',
            'file' => 'mimes:jpeg, jpg, png, pdf',
            'email' => 'required|email',
            'primary_phone_number' => 'required|numeric',
            'secondary_phone_number' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        } else {

            $path = 'uploads/registration/applicant_identity';
            $fileName = (string)time() . '-' . preg_replace('/\s+/', '_', $request->file->getClientOriginalName());

            if ($request->has('file')) {
                $request->file->storeAs($path, $fileName, ['disk' => 'public']);
            }

            $fileUpload = Fileupload::create(['name' => $path."/".$fileName]);

            $model = new Applicant();
            $model->fill($request->input());
            $model->file = $fileUpload->id;
            if ($model->save()) {
                return ($model);
            }
        }
    }
}
