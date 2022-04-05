<?php

namespace App\Http\Controllers\API\v1\Vaccine;

use App\Enums\VaccineRequestStatusEnum;
use App\FileUpload;
use App\Models\Vaccine\VaccineRequest;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVaccineRequest;
use App\Http\Requests\VaccineRequest\GetVaccineRequest;
use App\Http\Requests\VaccineRequest\UpdateStatusVaccineRequest;
use App\Http\Resources\VaccineRequestResource;
use App\Mail\Vaccine\ConfirmEmailNotification;
use App\Models\Vaccine\VaccineRequestStatusNote;
use App\VaccineProductRequest;
use App\VaccineWmsJabar;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class VaccineRequestController extends Controller
{
    public function index(GetVaccineRequest $request)
    {
        $limit = $request->input('limit', 5);
        $data = VaccineRequest::filter($request)
            ->latest()
            ->sort($request);
        return VaccineRequestResource::collection($data->paginate($limit));
    }

    public function show($id, Request $request)
    {
        $data = VaccineRequest::with([
                'outbounds.outboundDetails'
            ])
            ->findOrFail($id);
        return response()->format(Response::HTTP_OK, 'success', new VaccineRequestResource($data));
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
            $response = response()->format(Response::HTTP_CREATED, 'success', $data);
            Mail::to($request->input('email'))->send(new ConfirmEmailNotification($data));
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception->getTrace());
        }
        return $response;
    }

    public function update(VaccineRequest $vaccineRequest, UpdateStatusVaccineRequest $request)
    {
        switch ($request->status) {
            case VaccineRequestStatusEnum::verified():
                VaccineRequestStatusNote::insertData($request, $vaccineRequest->id);
                break;

            case VaccineRequestStatusEnum::integrated():
                $response = VaccineWmsJabar::sendVaccineRequest($vaccineRequest);
                if ($response->getStatusCode() != Response::HTTP_OK) {
                    return $response;
                }
                break;
        }

        $vaccineRequest->fill($request->validated());
        $data[$request->status . '_at'] = Carbon::now();
        $data[$request->status . '_by'] = auth()->user()->id;
        $vaccineRequest->update($data);

        return response()->format(Response::HTTP_OK, 'Vaccine request updated');
    }
}
