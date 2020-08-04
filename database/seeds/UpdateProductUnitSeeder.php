<?php

use Illuminate\Database\Seeder;

class UpdateProductUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** 
         * Get New Product List 
         */
        $newProducts = \App\Product::where('material_group_status', 1)->select(DB::raw('id, 1 as unit_id, "'.date('Y-m-d H:i:s').'" as created_at'));
        
        /** 
         * get the binding parameters
         */
        $bindings = $newProducts->getBindings();
        
        /**
         * now go down to the "Network Layer"
         * and do a hard coded select
         */
        $insertQuery = 'INSERT into product_unit (product_id, unit_id, created_at) '
        . $newProducts->toSql();

        DB::insert($insertQuery, $bindings);
    }
}
