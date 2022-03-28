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
    $status = $request->input('status');

    $data = $this->setData($status);

    return [
        'id' => $this->id,
        'vaccine_request_id' => $this->vaccine_request_id,
        'product_id' => $data['productId'],
        'category' => $this->category,
        'product_name' => $data['productName'],
        'quantity' => $data['quantity'],
        'unit' => $data['unit'],
        'usage' => $this->usage,
        'description' => $this->description,
        'product_status' => $data['productStatus'],
        'note' => $this->note,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at
    ];
  }

  public function setData($status)
  {
    $data['productId'] = $this->product_id;
    $data['productName'] = $this->vaccineProduct->name ?? "-";
    $data['quantity'] = $this->quantity;
    $data['unit'] = $this->unit;
    $data['productStatus'] = null;

    if ($status == 'recommendation') {
        $data['productId'] = $this->recommendation_product_id ?? $data['productId'];
        $data['productName'] = $this->recommendation_product_name ?? $data['productName'];
        $data['quantity'] = $this->recommendation_quantity ?? $data['quantity'];
        $data['unit'] = $this->recommendation_UoM ?? $data['unit'];
        $data['productStatus'] = $this->recommendation_status;
    } elseif ($status == 'finalization') {
        $data['productId'] = $this->finalization_product_id ?? $this->recommendation_product_id;
        $data['productName'] = $this->finalization_product_name ?? $this->recommendation_product_name;
        $data['quantity'] = $this->finalization_quantity ?? $this->recommendation_quantity;
        $data['unit'] = $this->finalization_UoM ?? $this->recommendation_UoM;
        $data['productStatus'] = $this->finalization_status;
    }

    return $data;
  }
}
