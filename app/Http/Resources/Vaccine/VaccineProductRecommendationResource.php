<?php

namespace App\Http\Resources\Vaccine;

use Illuminate\Http\Resources\Json\JsonResource;

class VaccineProductRecommendationResource extends JsonResource
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
        'product_id' => $this->recommendation_product_id ?? $this->product_id,
        'product_name' => $this->recommendation_product_name ?? optional($this->vaccineProduct)->name,
        'quantity' => $this->recommendation_quantity ?? $this->quantity,
        'unit' => $this->recommendation_UoM ?? $this->unit,
        'product_status' => $this->recommendation_status,
        'recommendation_note' => $this->recommendation_note,
        'category' => $this->category,
        'usage' => $this->usage,
        'description' => $this->description,
        'note' => $this->note,
        'reason' => $this->recommendation_reason,
        'file_url' => $this->recommendation_file_url,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
        'recommendation_date' => $this->recommendation_date,
        'finalized_date' => $this->finalized_date,
    ];
  }
}
