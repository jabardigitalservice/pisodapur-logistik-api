<?php

namespace App;

use DB;
use App\LogisticRealizationItems;

class Tracking
{
    static function selectFields()
    {
        return $select = [
            DB::raw('IFNULL(logistic_realization_items.id, needs.id) as id'),
            'needs.id as need_id',
            'logistic_realization_items.id as realization_id',

            'needs.product_id as need_product_id',
            'products.name as need_product_name',
            'needs.brand as need_description',
            'needs.quantity as need_quantity',
            'needs.unit as need_unit_id',
            'master_unit.unit as need_unit_name',
            'needs.usage as need_usage',
            'products.category',

            'logistic_realization_items.product_id as recommendation_product_id',
            'logistic_realization_items.product_name as recommendation_product_name',
            'logistic_realization_items.realization_quantity as recommendation_quantity',
            'realization_unit as recommendation_unit_name',
            'logistic_realization_items.recommendation_at',
            'logistic_realization_items.status as recommendation_status',
            
            'logistic_realization_items.final_product_id',
            'logistic_realization_items.final_product_name',
            'logistic_realization_items.final_quantity',
            'logistic_realization_items.final_unit',
            'logistic_realization_items.final_date',
            'logistic_realization_items.final_status'
        ];
    }

    static function getJoin($data, $isByAdmin)
    {
        $joinType = $isByAdmin ? 'left' : 'right';
        return $data->join('needs', 'logistic_realization_items.need_id', '=', 'needs.id', $joinType)
        ->join('products', 'needs.product_id', '=', 'products.id', 'left')
        ->join('master_unit', 'needs.unit', '=', 'master_unit.id', 'left')
        ->join('wms_jabar_material', 'logistic_realization_items.product_id', '=', 'wms_jabar_material.material_id', 'left');
    }

    static function getLogisticRequest($select, $request, $id)
    {
        $data = LogisticRealizationItems::select($select);
        $data = self::getJoin($data, false);
        return $data->orderBy('needs.id')->where('needs.applicant_id', $id);
    }

    static function getLogisticAdmin($select, $request, $id)
    {
        $data = LogisticRealizationItems::select($select);
        $data = self::getJoin($data, true);
        return $data->whereNotNull('logistic_realization_items.created_by')
            ->orderBy('logistic_realization_items.id')
            ->where('logistic_realization_items.applicant_id', $id);
    }
}
