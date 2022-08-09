<?php

namespace App\Http\Controllers\API\v1\Vaccine;

use App\Enums\Vaccine\VerificationStatusEnum;
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
use App\Models\Vaccine\Archive;
use App\Models\Vaccine\VaccineRequestStatusNote;
use App\User;
use App\VaccineProductRequest;
use App\VaccineWmsJabar;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class VaccineRequestController extends Controller
{
    public function index(GetVaccineRequest $request)
    {
        $limit = $request->input('limit', 5);

        if ($request->page_type == 'archive') {
            $resource = $this->archiveList($request);
        } else {
            $data = VaccineRequest::filter($request)
                ->sort($request);
            $resource = VaccineRequestResource::collection($data->paginate($limit));
        }
        return $resource;
    }

    public function archiveList($request)
    {
        $limit = $request->input('limit', 5);
        $data = Archive::filter($request)
            ->sort($request);

        $data->select( 'id', 'delivery_plan_date', 'agency_name', 'is_letter_file_final', 'verification_status', 'note', 'status', 'status_rank');
        return VaccineRequestArchiveResource::collection($data->paginate($limit));
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
        $vaccineRequest->fill($request->validated());
        if ($request->status == VaccineRequestStatusEnum::integrated()) {
            $response = VaccineWmsJabar::sendVaccineRequest($vaccineRequest);
            if ($response->getStatusCode() != Response::HTTP_OK) {
                return $response;
            }
        }

        // Condition when WMS Poslog hit the APIs
        $user = auth()->user();
        if (!auth()->user()) {
            $user = User::where('username', 'poslog_caesar')->first();
        }

        $processName = ($request->status == VaccineRequestStatusEnum::rejected()) ? VaccineRequestStatusEnum::verified() : $request->status;
        $data[$processName . '_at'] = Carbon::now();
        $data[$processName . '_by'] = $user->id;

        VaccineRequestStatusNote::insertData($request, $vaccineRequest->id);
        $vaccineRequest->update($data);
        Artisan::call('vaccine-logistik:verification-status-generator');

        $status = ($request->vaccine_status_note && $request->status == VaccineRequestStatusEnum::verified()) ? VaccineRequestStatusEnum::verified_with_note() : $request->status;
        $this->sendEmailNotification($vaccineRequest, $status);
        return response()->format(Response::HTTP_OK, 'Vaccine request updated');
    }

    public function sendEmailNotification($vaccineRequest, $status = false)
    {
        $status = $status ?? $vaccineRequest->status;
        Mail::to($vaccineRequest->applicant_email)->send(new VerifiedEmailNotification($vaccineRequest, $status));
    }

    public function cito(VaccineRequest $vaccineRequest, Request $request)
    {
        $isCITO = !$vaccineRequest->is_cito;
        $vaccineRequest->is_cito = $isCITO;
        if ($isCITO) {
            $vaccineRequest->cito_by = auth()->user()->id;
            $vaccineRequest->cito_at = Carbon::now();
        }
        $vaccineRequest->save();

        return response()->format(Response::HTTP_OK, 'success', $vaccineRequest);
    }
}
