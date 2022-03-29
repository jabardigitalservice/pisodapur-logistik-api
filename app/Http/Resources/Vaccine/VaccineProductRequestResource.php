<?php

namespace App\Http\Resources\Vaccine;

use Illuminate\Http\Resources\Json\JsonResource;

class VaccineProductRequestResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $status = $request->input('status', 'request');
    return [
        'id' => $this->id,
        'vaccine_request_id' => $this->vaccine_request_id,
        'product_id' => $this->product_id,
        'product_name' => optional($this->vaccineProduct)->name,
        'quantity' => $this->quantity,
        'unit' => $this->unit,
        'product_status' =>null,
        'category' => $this->category,
        'usage' => $this->usage,
        'description' => $this->description,
        'note' => $this->note,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at
    ];
  }
}
