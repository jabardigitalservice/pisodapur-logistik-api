<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\FileUpload;
use Illuminate\Http\Request;

class LogisticRequest extends Model
{
    static function setRequestApplicant(Request $request)
    {
        $request['email'] = (!$request->input('email')) ? '' : $request->input('email', '');
        $request['applicants_office'] = (!$request->input('applicants_office')) ? '' : $request->input('applicants_office', '');
        if ($request->hasFile('applicant_file')) {
            $response = FileUpload::storeApplicantFile($request);
            $request['file'] = $response->id;
        }
        return $request;
    }

    static function setRequestEditLetter(Request $request, $id)
    {
        if ($request->hasFile('letter_file')) { //20
            $request['agency_id'] = $id;
            $response = FileUpload::storeLetterFile($request);
        }
        return $request;
    }

    static function saveData($model, Request $request)
    {
        unset($request['id']);
        $model->fill($request->all());
        $model->save();
        return response()->format(200, 'success');
    }
}
