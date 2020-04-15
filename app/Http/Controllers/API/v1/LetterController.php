<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Fileupload;
use App\Letter;
use Illuminate\Support\Facades\Storage;

class LetterController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agency_id' => 'required|numeric',
            'applicant_id' => 'required|numeric',
            'letter' => 'required|mimes:jpeg, jpg, png, pdf|max:10240'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        } else {

            $path = 'uploads/registration/letter';
            $fileName = (string)time() . '-' . preg_replace('/\s+/', '_', $request->letter->getClientOriginalName());

            if ($request->has('letter')) {
                $request->letter->storeAs($path, $fileName, ['disk' => 'public']);
            }

            $fileUpload = Fileupload::create(['name' => $path."/".$fileName]);

            $model = new Letter();
            $model->fill($request->input());
            $model->letter = $fileUpload->id;
            if ($model->save()) {
                return ($model);
            }
        }
    }
}
