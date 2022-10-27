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
        $integrated = $this->applicant->finalized_at;

        return [
            'is_urgency' => $this->applicant->is_urgency,
            'status' => $status,
            'step' => $this->getStep($status, $integrated),
            'info' => $this->getInfo($status),
            'agency' => $this->getAgency(),
            'applicant' => $this->applicant,
            'letter' => $this->letter,
            'master_faskes' => $this->masterFaskes,
        ];
    }

    public function getInfo($status)
    {
        $approvedBy = $this->applicant->finalizedBy->name ?? null;

        if (in_array($status, [TrackingStatusEnum::verified()->getName(), TrackingStatusEnum::verification_rejected()->getName()])) {
            $approvedBy = $this->applicant->verifiedBy->name ?? null;
        } elseif (in_array($status, [TrackingStatusEnum::approved()->getName(), TrackingStatusEnum::approval_rejected()->getName()])) {
            $approvedBy = $this->applicant->approvedBy->name ?? null;
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
            'master_faskes_id' => $this->master_faskes_id,
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

    public function getStep($status, $integrated)
    {
        $step = 'administrasi';

        if ($integrated && $status == TrackingStatusEnum::finalized()->getName()) {
            $step = 'final';
        } elseif (in_array($status, [TrackingStatusEnum::approved()->getName(), TrackingStatusEnum::finalized()->getName()])) {
            $step = 'realisasi';
        } elseif ($status == TrackingStatusEnum::approval_rejected()->getName()) {
            $step = 'ditolak rekomendasi';
        }

        return $step;
    }
}
