<?php

namespace App\Traits;

trait TransformTrait
{
    public function getDataTransform($data, $phase = 'administration')
    {
        $data->getCollection()->transform(function ($item) use ($phase) {
            $item->status = !$item->status ? 'not_approved' : $item->status;
            $item->final_status = !$item->final_status ? 'not_approved' : $item->final_status;

            switch ($phase) {
                case 'recommendation':
                    $response = [
                        'product_id' => $item->recommendation_product_id,
                        'product_name' => $item->recommendation_product_name,
                        'unit' => $item->recommendation_unit,
                        'date' => $item->recommendation_date,
                        'quantity' => $item->recommendation_quantity,
                        'status' => $item->status,
                    ];

                    if ($item->status == 'not_approved') {
                        $response = [
                            'product_id' => $item->recommendation_product_id ?? $item->product_id,
                            'product_name' => $item->recommendation_product_name ?? $item->product_name,
                            'unit' => $item->recommendation_unit ?? $item->unit,
                            'date' => null,
                            'quantity' => null,
                            'status' => $item->status,
                        ];
                    }
                    break;
                case 'realization':
                    $response = [
                        'product_id' => $item->final_product_id,
                        'product_name' => $item->final_product_name,
                        'unit' => $item->final_unit,
                        'date' => $item->final_date,
                        'quantity' => $item->final_quantity,
                        'status' => $item->final_status,
                    ];

                    if ($item->final_status == 'not_approved') {
                        $response = [
                            'product_id' => $item->final_product_id ?? $item->recommendation_product_id,
                            'product_name' => $item->final_product_name ?? $item->recommendation_product_name,
                            'unit' => $item->final_unit ?? $item->recommendation_unit,
                            'date' => $item->final_date ?? $item->recommendation_date,
                            'quantity' => null,
                            'status' => $item->final_status ?? $item->status,
                        ];
                    }
                    break;
                default:
                    $response = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'unit' => $item->unit,
                        'date' => $item->date,
                        'quantity' => $item->quantity,
                        'status' => $item->status
                    ];
                    break;
            }
            return $response + [
                'brand' => $item->brand,
                'category' => $item->category,
                'request_quantity' => $item->request_quantity,
            ];
        });

        return $data;
    }
}
