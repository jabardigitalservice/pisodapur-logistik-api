<?php

namespace App\Traits;

trait SelectTrait
{
    public function selectNeed()
    {
        $data = [
            'needs.id',
            'needs.product_id',
            'products.name as product_name',
            'needs.quantity',
            'needs.unit',
            'needs.usage',
            'products.category',
            'logistic_realization_items.status',
            'administration.name as pic',
        ];
        return $data;
    }

    public function selectRecommendation()
    {
        $data = [
            'logistic_realization_items.product_id as product_id',
            'logistic_realization_items.product_name as product_name',
            'logistic_realization_items.realization_quantity as quantity',
            'logistic_realization_items.realization_unit as unit',
            'logistic_realization_items.realization_date as date',
            'logistic_realization_items.status as status',
            'recommendation.name as pic',
        ];

        return $data;
    }

    public function selectRealization()
    {
        $data = [
            'logistic_realization_items.final_product_id as product_id',
            'logistic_realization_items.final_product_name as product_name',
            'logistic_realization_items.final_quantity as quantity',
            'logistic_realization_items.final_unit as unit',
            'logistic_realization_items.final_date as date',
            'logistic_realization_items.final_status as status',
            'realization.name as pic',
        ];

        return $data;
    }
}
