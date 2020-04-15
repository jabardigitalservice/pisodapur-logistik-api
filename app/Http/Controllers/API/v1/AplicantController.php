<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Aplicant;
use App\Fileupload;
use Illuminate\Support\Facades\Storage;

class AplicantController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'aplicant_name' => 'required|string',
            'aplicants_office' => 'required|string',
            'file' => 'mimes:jpeg,jpg,png,pdf',
            'email' => 'required|email',
            'primary_phone_number' => 'required|numeric',
            'secondary_phone_number' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        } else {

            $path = 'uploads/registration/aplicant_identity';
            $fileName = (string)time() . '-' . preg_replace('/\s+/', '_', $request->file->getClientOriginalName());

            if ($request->has('file')) {
                $request->file->storeAs($path, $fileName, ['disk' => 'public']);
            }

            $fileUpload = Fileupload::create(['name' => $path."/".$fileName]);

            $model = new Aplicant();
            $model->fill($request->input());
            $model->file = $fileUpload->id;
            if ($model->save()) {
                return ($model);
            }
        }
    }
}
