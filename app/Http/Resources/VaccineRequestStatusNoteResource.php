<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VaccineRequestStatusNoteResource extends JsonResource
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
        'id' => $this->vaccine_status_note_id,
        'name' => optional($this->vaccineStatusNote)->name
      ];
  }
}
