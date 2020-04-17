<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\FileUpload;
use App\Letter;
use Illuminate\Support\Facades\Storage;

class LetterController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agency_id' => 'required|numeric',
            'applicant_id' => 'required|numeric',
            'letter' => 'required|mimes:jpeg,jpg,png,pdf|max:10240'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        } else {

            if ($request->has('letter')) {
                $path = Storage::disk('s3')->put('registration/letter', $request->letter);
            }
            $fileUpload = FileUpload::create(['name' => $path]);

            $model = new Letter();
            $model->fill($request->input());
            $model->letter = $fileUpload->id;
            if ($model->save()) {
                $model->letter = Storage::disk('s3')->url($fileUpload->name);
                return ($model);
            }
        }
    }
}
