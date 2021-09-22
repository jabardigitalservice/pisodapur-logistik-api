<?php

namespace App\Http\Controllers\API\v1;

use App\FileUpload;
use App\VaccineRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVaccineRequest;
use App\VaccineProductRequest;
use DB;
use Illuminate\Support\Facades\Storage;

class VaccineRequestController extends Controller
{
    public function store(StoreVaccineRequest $request)
    {
        DB::beginTransaction();
        try {
            $request->merge(['letter_file_url' => Storage::put(FileUpload::LETTER_PATH, $request->file('letter_file'))]);
            $request->merge(['applicant_file_url' => Storage::put(FileUpload::APPLICANT_IDENTITY_PATH, $request->file('applicant_file'))]);
            $data = VaccineRequest::add($request);
            $request->merge(['vaccine_request_id' => $data->id]);
            $data['need'] = VaccineProductRequest::add($request);
            $response = response()->format(Response::HTTP_OK, 'success', $data);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception->getTrace());
        }
        return $response;
    }
}
