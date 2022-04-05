<?php

namespace App\Http\Resources\Vaccine;

use Illuminate\Http\Resources\Json\JsonResource;

class VaccineProductFinalizationResource extends JsonResource
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
        'product_id' => $this->finalized_product_id ?? $this->recommendation_product_id,
        'product_name' => $this->finalized_product_name ?? $this->recommendation_product_name,
        'quantity' => $this->finalized_quantity ?? $this->recommendation_quantity,
        'unit' => $this->finalized_UoM ?? $this->recommendation_UoM,
        'product_status' => $this->finalized_status,
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
