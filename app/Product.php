<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $fillable = [
        'name', 'description', 'total_stock', 'total_used', 'is_imported'
    ];

    public function productUnit()
    {
        return $this->hasMany('App\ProductUnit', 'product_id');
    }

    public function need()
    {
        return $this->belongsToOne('App\Needs', 'product_id', 'id');
    }

    public function unit()
    {
        return $this->hasOne('App\MasterUnit', 'id', 'unit');
    }
}
