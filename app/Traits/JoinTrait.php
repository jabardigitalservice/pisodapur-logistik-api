<?php

namespace App\Traits;

trait JoinTrait
{
    public function scopeJoinProduct($query, $table, $field)
    {
        return $query->leftjoin('products', 'products.id', $table . '.' . $field);
    }

    public function scopeJoinLogisticRealizationItem($query)
    {
        return $query->leftjoin('logistic_realization_items', 'logistic_realization_items.need_id', 'needs.id');
    }

    public function scopeJoinUser($query, $alias, $foreignKey)
    {
        return $query->leftjoin('users as ' . $alias, $alias . '.id', 'logistic_realization_items.' . $foreignKey);
    }
}
