<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VaccineRequestArchiveResource extends JsonResource
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
        'agency_name' => $this->agency_name,
        'delivery_plan_date' => $this->delivery_plan_date,
        'is_letter_file_final' => $this->is_letter_file_final,
        'note' => $this->note,
        'vaccine_request_status_notes' => VaccineRequestStatusNoteResource::collection($this->vaccineRequestStatusNotes),
        'status' => $this->status,
      ];
  }
}
