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

class LogisticRequestController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    //param for agency
                    'agency_type' => 'required|string',
                    'agency_name' => 'required',
                    'phone_number' => 'numeric',
                    'location_district_code' => 'required|string',
                    'location_subdistrict_code' => 'required|string',
                    'location_village_code' => 'required|string',
                    'location_address' => 'required|string',

                    //pram for applicant
                    'applicant_name' => 'required|string',
                    'applicant_office' => 'required|string',
                    'applicant_file' => 'required|mimes:jpeg,jpg,png,pdf|max:10240',
                    'email' => 'required|email',
                    'primary_phone_number' => 'required|numeric',
                    'secondary_phone_number' => 'required|numeric',

                    // param for need
                    'logistic_request' => 'required|array'
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            try {
                $agency = $this->agencyStore($request);
                $request->request->add(['agency_id' => $agency->id]);

                $applicant = $this->applicantStore($request);
                $request->request->add(['applicant_id' => $applicant->id]);

                $need = $this->needStore($request);
            } catch (\Exception $exception) {
                return response()->json(['error' => $exception->getMessage()], 400);
            }
        }
    }

    public function agencyStore($request)
    {
        try {
            $agency = Agency::create([
                'agency_type' => $request->input('agency_type'),
                'agency_name' => $request->input('agency_name'),
                'phone_number' => $request->input('phone_number'),
                'location_district_code' => $request->input('location_district_code'),
                'location_subdistrict_code' => $request->input('location_subdistrict_code'),
                'location_village_code' => $request->input('location_village_code'),
                'location_address' => $request->input('location_address'),
            ]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }

        return $agency;
    }

    public function applicantStore($request)
    {

        try {

            if ($request->hasFile('applicant_file')) {

                $file = $request->file('applicant_file');
                if ($file->getSize() > 5000000) {
                    return response()->json(['errors' => 'Gambar yang diupload maximal 5MB!'], 422);
                }

                if (!in_array($file->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'pdf'])) {
                    return response()->json(['errors' => 'Gambar yang diupload harus format pdf, jpeg, jpg dan png!'], 422);
                }

                $fileName = 'applicant_identity_' . uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();

                $path = Storage::disk('s3')->put('registration/applicant_identity', $fileName);
                $fileUpload = FileUpload::create(['name' => $path]);

                $applicant = Applicant::create([
                    'agency_id' => $request->input('agency_id'),
                    'applicant_name' => $request->input('applicant_name'),
                    'applicants_office' => $request->input('applicant_office'),
                    'file' => $fileUpload->id,
                    'email' => $request->input('email'),
                    'primary_phone_number' => $request->input('primary_phone_number'),
                    'secondary_phone_number' => $request->input('secondary_phone_number'),
                ]);
            }
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }

        return $applicant;
    }

    public function needStore($request)
    {
        try {
            foreach ($request->input('logistic_request') as $key => $value) {
                $need = Needs::create([
                    'agency_id' => $request->input('agency_id'),
                    'applicant_id' => $request->input('applicant_id'),
                    'item' => $value['item'],
                    'brand' => $value['brand'],
                    'quantity' => $value['quantity'],
                    'unit' => $value['unit'],
                    'usage' => $value['usage'],
                    'priority' => $value['priority']
                ]);
            }
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }

        return true;
    }
}
