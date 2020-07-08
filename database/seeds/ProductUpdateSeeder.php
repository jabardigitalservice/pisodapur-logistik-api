<?php

use Illuminate\Database\Seeder;

class ProductUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Product::where('material_group_status', '=', 1)->update(['material_group_status' => NULL]);
        $filepath = base_path('database/seeds/data/product_update.sql');
        DB::unprepared(file_get_contents($filepath));
    }
}
