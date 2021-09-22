<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VaccineProductRequest extends Model
{
    protected $fillable = [
        'vaccine_request_id',
        'product_id',
        'quantity',
        'unit_id',
        'description',
        'usage',
    ];

    static function add($request)
    {
        foreach (json_decode($request->input('logistic_request'), true) as $key => $value) {
            $vaccineProductRequest = [
                'vaccine_request_id' => $request->input('vaccine_request_id'),
                'product_id' => $value['product_id'],
                'description' => $value['description'],
                'quantity' => $value['quantity'],
                'unit_id' => $value['unit_id'],
                'usage' => $value['usage'],
            ];
            $response[] = VaccineProductRequest::create($vaccineProductRequest);
        }
        return $response;
    }
}
