<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PoslogProduct extends Model
{    
    const DEFAULT_UOM = 'PCS';
    const DEFAULT_STOCK = 0;

    protected $fillable = [
        'material_id', 'material_name', 'soh_location', 'soh_location_name', 'uom', 'matg_id', 'stock_ok', 'stock_nok'
    ];

    public function setUomAttribute($value)
    {
        return $value ? $value : self::DEFAULT_UOM;
    }

    public function setStockOkAttribute($value)
    {
        return $value ? $value : self::DEFAULT_STOCK;
    }

    public function setStockNokAttribute($value)
    {
        return $value ? $value : self::DEFAULT_STOCK;
    }

    public function getUomAttribute($value)
    {
        return $value ? $value : self::DEFAULT_UOM;
    }

    public function getStockOkAttribute($value)
    {
        return $value ? number_format($value, 0, ",", ".") : self::DEFAULT_STOCK;
    }

    public function getStockNokAttribute($value)
    {
        return $value ? number_format($value, 0, ",", ".") : self::DEFAULT_STOCK;
    }
}
