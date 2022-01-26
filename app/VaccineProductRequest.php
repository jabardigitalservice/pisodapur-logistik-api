<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VaccineProductRequest extends Model
{
    protected $fillable = [
        'vaccine_request_id',
        'product_id',
        'quantity',
        'unit',
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
                'unit' => $value['unit'],
                'usage' => $value['usage'],
            ];
            $response[] = VaccineProductRequest::create($vaccineProductRequest);
        }
        return $response;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit');
    }
}
