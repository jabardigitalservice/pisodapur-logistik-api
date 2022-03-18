<?php

namespace App;

use App\Models\Vaccine\VaccineProduct;
use Illuminate\Database\Eloquent\Model;

class VaccineProductRequest extends Model
{
    protected $with = ['vaccineProduct:id,name,category'];

    protected $fillable = [
        'vaccine_request_id',
        'product_id',
        'category',
        'quantity',
        'unit',
        'description',
        'usage',
        'note',
        'recommendation_product_id',
        'recommendation_product_name',
        'recommendation_quantity',
        'recommendation_UoM',
        'recommendation_date',
        'recommendation_status',
        'recommendation_by',
        'finalized_product_id',
        'finalized_product_name',
        'finalized_quantity',
        'finalized_UoM',
        'finalized_date',
        'finalized_status',
        'finalized_by'
    ];

    static function add($request)
    {
        foreach (json_decode($request->input('logistic_request'), true) as $key => $value) {
            $vaccineProductRequest = [
                'vaccine_request_id' => $request->input('vaccine_request_id'),
                'product_id' => $value['product_id'],
                'category' => $value['category'],
                'description' => $value['description'],
                'quantity' => $value['quantity'],
                'unit' => $value['unit'],
                'usage' => $value['usage'],
                // 'note' => optional($value['note']),
            ];
            $response[] = VaccineProductRequest::create($vaccineProductRequest);
        }
        return $response;
    }

    public function vaccineProduct()
    {
        return $this->belongsTo(VaccineProduct::class, 'product_id');
    }

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit');
    }
}
