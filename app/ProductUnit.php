<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $table = 'product_unit';

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id');
    } 
}
