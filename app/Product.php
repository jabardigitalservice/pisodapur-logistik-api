<?php

namespace App;

use App\Enums\ProductCategoryEnum;
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
        return $this->hasMany('App\Needs', 'product_id', 'id');
    }

    public function unit()
    {
        return $this->hasOne('App\MasterUnit', 'id', 'unit');
    }

    static function getFirst($id)
    {
        try {
            $data = self::findOrFail($id);
        } catch (\Exception $exception) {
            $data = [];
        }
        return $data;
    }

    public function scopeCategoryFilter($query, $request)
    {
        if ($request->has('category')) {
            return $query->where('products.category', $request->input('category'));
        } else {
            return $query->where('products.category', '!=', ProductCategoryEnum::vaksin());
        }
    }
}
