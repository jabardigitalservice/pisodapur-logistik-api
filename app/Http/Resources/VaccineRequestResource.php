<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VaccineRequestResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
      return [
        'id' => $this->id,
        'agency_id' => $this->agency_id,
        'agency_name' => $this->masterFaskes->nama_faskes,
        'agency_type_id' => $this->agency_type_id,
        'agency_type_name' => $this->masterFaskesType->name,
        'agency_phone_number' => $this->agency_phone_number,
        'agency_address' => $this->agency_address,
        'agency_village_id' => $this->agency_village_id,
        'agency_village_name' => $this->village->kemendagri_desa_nama,
        'agency_district_id' => $this->agency_district_id,
        'agency_district_name' => $this->village->kemendagri_kecamatan_nama,
        'agency_city_id' => $this->agency_city_id,
        'agency_city_name' => $this->village->kemendagri_kabupaten_nama,

        'applicant_fullname' => $this->applicant_fullname,
        'applicant_position' => $this->applicant_position,
        'applicant_email' => $this->applicant_email,
        'applicant_primary_phone_number' => $this->applicant_primary_phone_number,
        'applicant_secondary_phone_number' => $this->applicant_secondary_phone_number,
        'applicant_file_url' => $this->applicant_file_url,
        'is_letter_file_final' => $this->is_letter_file_final,

        'letter_number' => $this->letter_number,
        'letter_file_url' => $this->letter_file_url,
        'status' => $this->status,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
        'verified_at' => $this->verified_at,
        'verified_by' => $this->verifiedBy,
        'approved_at' => $this->approved_at,
        'approved_by' => $this->approvedBy,
        'finalized_at' => $this->finalized_at,
        'finalized_by' => $this->finalizedBy,
        'is_reference' => $this->masterFaskes->is_reference,
        'is_completed' => $this->is_completed,
        'is_urgency' => $this->is_urgency,
      ];
  }
}
