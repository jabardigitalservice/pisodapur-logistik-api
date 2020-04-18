<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;
use App\Needs;
use App\Agency;
use App\Applicant;
use App\Fileupload;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\LogisticRequestResource;
use App\Letter;

class LogisticRequestController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'master_faskes_id' => 'required|numeric',
                    'agency_type' => 'required|string',
                    'agency_name' => 'required',
                    'phone_number' => 'numeric',
                    'location_district_code' => 'required|string',
                    'location_subdistrict_code' => 'required|string',
                    'location_village_code' => 'required|string',
                    'location_address' => 'required|string',
                    'applicant_name' => 'required|string',
                    'applicant_office' => 'required|string',
                    'applicant_file' => 'required|mimes:jpeg,jpg,png,pdf|max:5000',
                    'email' => 'required|email',
                    'primary_phone_number' => 'required|numeric',
                    'secondary_phone_number' => 'required|numeric',
                    'logistic_request' => 'required|array',
                    'letter' => 'required|mimes:jpeg,jpg,png,pdf|max:10240'
                ]
            )
        );

        if ($validator->fails()) {
            return response()->format(422, $validator->errors());
        } else {
            try {
                $agency = $this->agencyStore($request);
                $request->request->add(['agency_id' => $agency->id]);

                $applicant = $this->applicantStore($request);
                $request->request->add(['applicant_id' => $applicant->id]);

                $need = $this->needStore($request);
                $letter = $this->letterStore($request);

                $response = array(
                    'agency' => $agency,
                    'applicant' => $applicant,
                    'need' => $need,
                    'letter' => $letter
                );
            } catch (\Exception $exception) {
                return response()->format(400, $exception->getMessage());
            }
        }

        return response()->format(200, 'success', new LogisticRequestResource($response));
    }

    public function agencyStore($request)
    {
        try {
            $agency = Agency::create([
                'master_faskes_id' => $request->input('master_faskes_id'),
                'agency_type' => $request->input('agency_type'),
                'agency_name' => $request->input('agency_name'),
                'phone_number' => $request->input('phone_number'),
                'location_district_code' => $request->input('location_district_code'),
                'location_subdistrict_code' => $request->input('location_subdistrict_code'),
                'location_village_code' => $request->input('location_village_code'),
                'location_address' => $request->input('location_address'),
            ]);
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return $agency;
    }

    public function applicantStore($request)
    {
        $fileUploadId = null;
        try {

            if ($request->hasFile('applicant_file')) {
                $path = Storage::disk('s3')->put('registration/applicant_identity', $request->applicant_file);
                $fileUpload = FileUpload::create(['name' => $path]);
                $fileUploadId = $fileUpload->id;
            }

            $applicant = Applicant::create([
                'agency_id' => $request->input('agency_id'),
                'applicant_name' => $request->input('applicant_name'),
                'applicants_office' => $request->input('applicant_office'),
                'file' => $fileUploadId,
                'email' => $request->input('email'),
                'primary_phone_number' => $request->input('primary_phone_number'),
                'secondary_phone_number' => $request->input('secondary_phone_number'),
            ]);

            $applicant->file_path = Storage::disk('s3')->url($fileUpload->name);
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return $applicant;
    }

    public function needStore($request)
    {
        $response = [];
        try {
            foreach ($request->input('logistic_request') as $key => $value) {
                $need = Needs::create([
                    'agency_id' => $request->input('agency_id'),
                    'applicant_id' => $request->input('applicant_id'),
                    'product_id' => $value['product_id'],
                    'brand' => $value['brand'],
                    'quantity' => $value['quantity'],
                    'unit' => $value['unit'],
                    'usage' => $value['usage'],
                    'priority' => $value['priority']
                ]);

                $response[] = $need;
            }
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return $response;
    }

    public function letterStore($request)
    {
        $fileUploadId = null;
        try {

            if ($request->hasFile('letter')) {
                $path = Storage::disk('s3')->put('registration/letter', $request->letter);
                $fileUpload = FileUpload::create(['name' => $path]);
                $fileUploadId = $fileUpload->id;
            }

            $letter = Letter::create([
                'agency_id' => $request->input('agency_id'),
                'applicant_id' => $request->input('applicant_id'),
                'letter' => $fileUploadId,
            ]);

            $letter->file_path = Storage::disk('s3')->url($fileUpload->name);
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return $letter;
    }
}
