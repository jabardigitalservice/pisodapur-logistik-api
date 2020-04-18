<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {

   protected $fillable = [
        'name', 'description', 'total_stock', 'total_used'
    ];

    public function productUnit()
    {
        return $this->hasMany('App\ProductUnit', 'product_id');
    } 
}
