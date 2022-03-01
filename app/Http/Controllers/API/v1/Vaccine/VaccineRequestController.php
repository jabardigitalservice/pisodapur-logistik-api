<?php

namespace App\Http\Controllers\API\v1\Vaccine;

use App\Enums\VaccineRequestStatusEnum;
use App\FileUpload;
use App\Models\Vaccine\VaccineRequest;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVaccineRequest;
use App\Http\Requests\VaccineRequest\GetVaccineRequest;
use App\Http\Requests\VaccineRequest\UpdateVaccineRequest;
use App\Http\Resources\VaccineRequestResource;
use App\VaccineProductRequest;
use App\VaccineWmsJabar;
use Carbon\Carbon;
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
            'village',
            'verifiedBy:id,name',
            'approvedBy:id,name',
            'finalizedBy:id,name'
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
            'village',
            'verifiedBy:id,name',
            'approvedBy:id,name',
            'finalizedBy:id,name',
            'outbounds.outboundDetails'
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
            $response = response()->format(Response::HTTP_CREATED, 'success', $data);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage(), $exception->getTrace());
        }
        return $response;
    }

    public function update(VaccineRequest $vaccineRequest, UpdateVaccineRequest $request)
    {
        $vaccineRequest->fill($request->validated());
        if ($request->status == VaccineRequestStatusEnum::finalized()) {
            return $this->sendToPoslog($vaccineRequest);
        }
        $this->setUpdateByStatus($vaccineRequest, $request);
        return response()->format(Response::HTTP_OK, 'Vaccine request updated');
    }

    public function setUpdateByStatus($vaccineRequest, $request)
    {
        $user = auth()->user();
        if (in_array($request->status, [VaccineRequestStatusEnum::verified(), VaccineRequestStatusEnum::verification_rejected()])) {
            $vaccineRequest->verified_at = Carbon::now();
            $vaccineRequest->verified_by = $user->id;
        } else if (in_array($request->status, [VaccineRequestStatusEnum::approved(), VaccineRequestStatusEnum::approval_rejected()])) {
            $vaccineRequest->approved_at = Carbon::now();
            $vaccineRequest->approved_by = $user->id;
        }
        $vaccineRequest->save();
    }

    public function sendToPoslog($vaccineRequest)
    {
        $user = auth()->user();
        $response = VaccineWmsJabar::sendVaccineRequest($vaccineRequest);

        if ($response->getStatusCode() == Response::HTTP_OK) {
            $vaccineRequest->finalized_at = Carbon::now();
            $vaccineRequest->finalized_by = $user->id;
            $vaccineRequest->is_integrated = 1;
            $vaccineRequest->is_completed = 1;
            $vaccineRequest->save();
        }
        return $response;
    }
}
