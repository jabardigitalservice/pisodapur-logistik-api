<?php

namespace App\Traits;

trait SelectTrait
{
    public function selectNeed()
    {
        $data = [
            'logistic_realization_items.id',
            'needs.id as need_id',
            'needs.product_id',
            'products.name as product_name',
            'needs.quantity',
            'master_unit.unit',
            'needs.brand',
            'products.category',
            'logistic_realization_items.status',
        ];
        return $data;
    }

    public function selectRecommendation()
    {
        $data = [
            'logistic_realization_items.id as id',
            'logistic_realization_items.need_id',
            'logistic_realization_items.product_id as product_id',
            'logistic_realization_items.product_name as product_name',
            'logistic_realization_items.realization_quantity as quantity',
            'logistic_realization_items.realization_unit as unit',
            'logistic_realization_items.status as status',
        ];

        return $data;
    }

    public function selectRealization()
    {
        $data = [
            'logistic_realization_items.id',
            'logistic_realization_items.need_id',
            'logistic_realization_items.final_product_id as product_id',
            'logistic_realization_items.final_product_name as product_name',
            'logistic_realization_items.final_quantity as quantity',
            'logistic_realization_items.final_unit as unit',
            'logistic_realization_items.final_status as status',
        ];

        return $data;
    }
}
