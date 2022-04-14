<?php

namespace App\Http\Resources\Vaccine;

use Illuminate\Http\Resources\Json\JsonResource;

class VaccineProductDeliveryPlanResource extends JsonResource
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
        'product_id' => $this->finalized_product_id,
        'product_name' => $this->finalized_product_name,
        'quantity' => $this->finalized_quantity,
        'unit' => $this->finalized_UoM,
        'product_status' => $this->finalized_status,
        'recommendation_note' => $this->recommendation_note,
        'category' => $this->category,
        'usage' => $this->usage,
        'description' => $this->description,
        'note' => $this->note,
        'reason' => $this->recommendation_reason,
        'file_url' => $this->recommendation_file_url,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at
    ];
  }
}
