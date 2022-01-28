<?php

namespace App\Http\Controllers\API\v1;

use App\FileUpload;
use App\VaccineRequest;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVaccineRequest;
use App\Http\Requests\VaccineRequest\GetVaccineRequest;
use App\Http\Requests\VaccineRequest\UpdateVaccineRequest;
use App\Http\Resources\VaccineRequestResource;
use App\VaccineProductRequest;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VaccineRequestController extends Controller
{
    public function index(GetVaccineRequest $request)
    {
        $limit = $request->input('limit', 10);
        $data = VaccineRequest::with([
            'masterFaskes:id,nama_faskes,is_reference',
            'masterFaskesType:id,name',
            'village'
        ])
        ->filter($request)
        ->sort($request);
        return VaccineRequestResource::collection($data->paginate($limit));
    }

    public function show($id, Request $request)
    {
        $data = VaccineRequest::with([
            'masterFaskes:id,nama_faskes,is_reference',
            'masterFaskesType:id,name',
            'village'
        ])
        ->where('id', $id)
        ->firstOrFail();
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function store(StoreVaccineRequest $request)
    {
        DB::beginTransaction();
        try {
            $request->merge(['letter_file_url' => Storage::put(FileUpload::LETTER_PATH, $request->file('letter_file'))]);
            if ($request->hasFile('applicant_file')) {
                $request->merge(['applicant_file_url' => Storage::put(FileUpload::APPLICANT_IDENTITY_PATH, $request->file('applicant_file'))]);
            }
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

    public function update($id, UpdateVaccineRequest $request)
    {
        $vaccineRequest = VaccineRequest::findOrFail($id);
        $vaccineRequest->status = $request->status;
        $vaccineRequest->note = $request->note;
        $vaccineRequest->save();
        return response()->format(Response::HTTP_OK, 'Vaccine Request Updated');
    }
}
