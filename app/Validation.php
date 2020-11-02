<?php

namespace App;
use Validator;

class Validation
{    
    static function validate($request, $param)
    {
        $response = response()->format(200, 'success');
        $validator = Validator::make($request->all(), $param);
        if ($validator->fails()) {
            $response = response()->format(422, $validator->errors(), $param);
        }
        return $response;
    }

    static function defaultError()
    {
        return response()->format(422, 'Error Tidak Diketahui');
    }

    static function validateAgencyType($agencyType, $allowable)
    {
        $response = response()->json(['status' => 'fail', 'message' => 'agency_type_value_is_not_accepted']);
        if (in_array($agencyType, $allowable)) { //allowable agency_type: {agency_type 4 => Masyarakat Umum , agency_type 5 => Instansi Lainnya}
            $response = response()->format(200, 'success');
        }
        return $response;
    }

    static function completenessDetail($data)
    {
        $data->getCollection()->transform(function ($item, $key) {
            $completeness = 0;
            $validCompleted = 7;
            if ($item->applicant->applicant_name) {
                $completeness++;
            }

            if ($item->agency_name) {
                $completeness++;
            }

            if ($item->location_address) {
                $completeness++;
            }

            if ($item->applicant->primary_phone_number) {
                $completeness++;
            }

            if ($item->applicant->secondary_phone_number) {
                $completeness++;
            }

            if ($item->applicant->letter) {
                $completeness++;
            }

            if ($item->applicant->file) {
                $completeness++;
            }

            $item->completeness = ($completeness === $validCompleted);
            return $item;
        });
    }
}
