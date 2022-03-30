<?php

namespace App\Http\Resources\Vaccine;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryPlanResource extends JsonResource
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
        'delivery_plan_date' => $this->delivery_plan_date,
        'created_at' => $this->created_at,
        'vaccine_sprint_letter_number' => optional($this->vaccineSprint)->letter_number,
        'id' => $this->id,
        'letter_number' => $this->letter_number,
        'agency_name' => $this->medicalFacility->name,
        'agency_type_name' => $this->medicalFacilityType->name,
        'is_urgency' => $this->is_urgency,

        'finalized_at' => $this->finalized_at,
        'finalized_by' => $this->finalizedBy,

        'integrated_at' => $this->integrated_at,
        'integrated_by' => $this->integratedBy,

        'updated_at' => $this->updated_at,
        'vaccine_sprint_id' => $this->vaccine_sprint_id,
    ];
  }
}
