<?php

namespace App\Traits;

use App\Product;

trait TransformTrait
{
    public function getDataTransform($data)
    {
        $data->getCollection()->transform(function ($item) {
            if (!$item->product_name) {
                $product = Product::where('id', $item->product_id)->first();
                $item->product_name = $product ? $product->name : null;
            }
            $item->status = !$item->status ? 'not_approved' : $item->status;
            return $item;
        });

        return $data;
    }
}
