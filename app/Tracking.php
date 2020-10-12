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
            DB::raw('IFNULL(logistic_realization_items.product_id, needs.product_id) as product_id'),
            'needs.product_id as need_product_id',
            'logistic_realization_items.product_id as realization_product_id',
            DB::raw('IFNULL(logistic_realization_items.product_name, products.name) as product_name'),
            'products.name as need_product_name',
            'logistic_realization_items.product_name as realization_product_name',
            'needs.brand as need_description',
            DB::raw('IFNULL(logistic_realization_items.realization_quantity, needs.quantity) as quantity'),
            DB::raw('IFNULL(logistic_realization_items.realization_unit, master_unit.unit) as unit_name'),
            'needs.quantity as need_quantity',
            'needs.unit as need_unit_id',
            'master_unit.unit as need_unit_name',
            'needs.usage as need_usage',
            'products.category',
            'logistic_realization_items.realization_quantity as allocation_quantity',
            'logistic_realization_items.created_at as allocated_at',            
            'logistic_realization_items.realization_quantity',
            'realization_unit as realization_unit_name',
            'logistic_realization_items.created_at as realized_at',
            DB::raw('IFNULL(logistic_realization_items.status, "not_approved") as status')
        ];
    }

    static function getLogisticRequest($select, $request, $id)
    {
        return LogisticRealizationItems::select($select)
            ->join('needs', 'logistic_realization_items.need_id', '=', 'needs.id', 'right')
            ->join('products', 'needs.product_id', '=', 'products.id', 'left')
            ->join('master_unit', 'needs.unit', '=', 'master_unit.id', 'left')
            ->join('wms_jabar_material', 'logistic_realization_items.product_id', '=', 'wms_jabar_material.material_id', 'left')
            ->orderBy('needs.id');
    }

    static function getLogisticAdmin($select, $request, $id)
    {
        return $logisticRealizationItems = LogisticRealizationItems::select($select)
            ->join('needs', 'logistic_realization_items.need_id', '=', 'needs.id', 'left')
            ->join('products', 'needs.product_id', '=', 'products.id', 'left')
            ->join('master_unit', 'needs.unit', '=', 'master_unit.id', 'left')
            ->join('wms_jabar_material', 'logistic_realization_items.product_id', '=', 'wms_jabar_material.material_id', 'left')
            ->whereNotNull('logistic_realization_items.created_by')
            ->orderBy('logistic_realization_items.id')
            ->where('logistic_realization_items.applicant_id', $id);
    }
}
