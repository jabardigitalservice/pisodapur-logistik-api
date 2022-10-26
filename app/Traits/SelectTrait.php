<?php

namespace App\Traits;

trait SelectTrait
{

    public function selectRequestNeed()
    {
        return array_merge($this->selectNeed(), $this->selectChannelReccomendation());
    }

    public function selectChannelReccomendation()
    {
        return array_merge($this->selectRecommendation(), $this->selectRealization());
    }

    public function selectNeed()
    {
        $data = [
            'needs.id as need_id',
            'needs.product_id',
            'needs.product_id as need_product_id',
            'products.name as product_name',
            'needs.quantity',
            'needs.quantity as request_quantity',
            'master_unit.unit',
            'needs.brand',
            'products.category',
            'needs.created_at as date',
        ];
        return $data;
    }

    public function selectRecommendation()
    {
        $data = [
            'logistic_realization_items.id',
            'logistic_realization_items.status',
            'logistic_realization_items.product_id as recommendation_product_id',
            'logistic_realization_items.product_name as recommendation_product_name',
            'logistic_realization_items.realization_quantity as recommendation_quantity',
            'logistic_realization_items.realization_unit as recommendation_unit',
            'logistic_realization_items.realization_date as recommendation_date',
        ];

        return $data;
    }

    public function selectRealization()
    {
        $data = [
            'logistic_realization_items.final_product_id',
            'logistic_realization_items.final_product_name',
            'logistic_realization_items.final_quantity',
            'logistic_realization_items.final_unit',
            'logistic_realization_items.final_status',
            'logistic_realization_items.final_date',
        ];

        return $data;
    }
}
