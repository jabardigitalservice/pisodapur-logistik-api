<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationMaterial extends Model
{
    protected $appends = ['current_stock', 'current_stock_formatted'];
    public function getCurrentStockAttribute()
    {
        return $this->stock_ok - $this->booked_stock;
    }

    public function getCurrentStockFormattedAttribute()
    {
        return number_format($this->getCurrentStockAttribute(), 0, ',', '.');
    }
}
