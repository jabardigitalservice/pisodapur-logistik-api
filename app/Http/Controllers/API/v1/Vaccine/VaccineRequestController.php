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
use App\Http\Resources\VaccineRequestArchiveResource;
use App\Http\Resources\VaccineRequestResource;
use App\Mail\Vaccine\ConfirmEmailNotification;
use App\Mail\Vaccine\VerifiedEmailNotification;
use App\Models\MedicalFacility;
use App\Models\Vaccine\VaccineRequestStatusNote;
use App\Notifications\Vaccine\VaccineStatusNotification;
use App\User;
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
            ->sort($request);

        if ($request->page_type == 'archive') {
            $data->select( 'id', 'delivery_plan_date', 'agency_name', 'is_letter_file_final', 'note', 'status');
            $resource = VaccineRequestArchiveResource::collection($data->paginate($limit));
        } else {
            $resource = VaccineRequestResource::collection($data->paginate($limit));
        }
        return $resource;
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

            if ($request->agency_type == 99) {
                $medicalFacility = $this->addMedicalFacility($request);
                $request->merge(['master_faskes_id' => $medicalFacility->id]);
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

    public function addMedicalFacility($request)
    {
        return MedicalFacility::create([
            'name' => $request->agency_name,
            'medical_facility_type_id' => $request->agency_type,
            'city_id' => $request->location_district_code,
            'district_id' => $request->location_subdistrict_code,
            'village_id' => $request->location_village_code,
            'address' => $request->location_address,
            'phone' => $request->phone_number
        ]);
    }

    public function update(VaccineRequest $vaccineRequest, UpdateStatusVaccineRequest $request)
    {
        switch ($request->status) {
            case VaccineRequestStatusEnum::verified():
                VaccineRequestStatusNote::insertData($request, $vaccineRequest->id);

                $status = VaccineRequestStatusEnum::verified();
                if ($request->vaccine_status_note) {
                    $status = VaccineRequestStatusEnum::verified_with_note();
                }
                Mail::to($vaccineRequest->applicant_email)->send(new VerifiedEmailNotification($vaccineRequest, $status));
                break;

            case VaccineRequestStatusEnum::finalized():
                Mail::to($vaccineRequest->applicant_email)->send(new VerifiedEmailNotification($vaccineRequest, VaccineRequestStatusEnum::finalized()));
                break;

            case VaccineRequestStatusEnum::integrated():
                $response = VaccineWmsJabar::sendVaccineRequest($vaccineRequest);
                if ($response->getStatusCode() != Response::HTTP_OK) {
                    return $response;
                }
                break;
        }

        $this->updateProcess($vaccineRequest, $request);
        return response()->format(Response::HTTP_OK, 'Vaccine request updated');
    }

    public function updateProcess($vaccineRequest, $request)
    {
        $vaccineRequest->fill($request->validated());
        $data[$request->status . '_at'] = Carbon::now();

        $user = auth()->user();
        if (!auth()->user()) {
            Mail::to($vaccineRequest->applicant_email)->send(new VerifiedEmailNotification($vaccineRequest, $request->status));
            $user = User::where('username', 'poslog_caesar')->first();
        }
        $data[$request->status . '_by'] = $user->id;
        return $vaccineRequest->update($data);
    }
}
