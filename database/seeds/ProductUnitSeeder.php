<?php

use Illuminate\Database\Seeder;

class ProductUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\ProductUnit::truncate();
        $filepath = base_path('database/seeds/data/product_unit.sql');
        DB::unprepared(file_get_contents($filepath));
    }
}
