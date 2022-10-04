<?php

namespace App\Http\Resources;

use App\Enums\TrackingStatusEnum;
use Illuminate\Http\Resources\Json\JsonResource;

class LogisticRequestDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $status = $this->applicant->tracking_status->getName();
        return [
            'is_urgency' => $this->applicant->is_urgency,
            'status' => $status,
            'info' => $this->getInfo($status),
            'agency' => $this->getAgency(),
            'applicant' => $this->applicant,
            'letter' => $this->letter
        ];
    }

    public function getInfo($status)
    {
        $approvedBy = $this->applicant->finalizedBy->name ?? null;

        if (in_array($status, [TrackingStatusEnum::verified(), TrackingStatusEnum::verification_rejected()])) {
            $approvedBy = $$this->applicant->verifiedBy->name ?? null;
        } elseif (in_array($status, [TrackingStatusEnum::approved(), TrackingStatusEnum::approval_rejected()])) {
            $approvedBy = $$this->applicant->approvedBy->name ?? null;
        }

        return [
            'id' => $this->id,
            'approved_by' => $approvedBy,
            'created_at' => $this->created_at,
        ];
    }

    public function getAgency()
    {
        return [
            'id' => $this->id,
            'agency_name' => $this->agency_name,
            'phone_number' => $this->phone_number,
            'agency_type_id' => $this->agency_type,
            'agency_type_name' => $this->masterFaskesType->name ?? null,
            'city_id' => $this->location_district_code,
            'city_name' => $this->city->kemendagri_kabupaten_nama ?? null,
            'district_id' => $this->location_subdistrict_code,
            'district_name' => $this->subDistrict->kemendagri_kecamatan_nama ?? null,
            'village_id' => $this->location_village_code,
            'village_name' => $this->village->kemendagri_desa_nama ?? null,
            'address' => $this->location_address,
            'is_reference' => $this->is_reference,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
