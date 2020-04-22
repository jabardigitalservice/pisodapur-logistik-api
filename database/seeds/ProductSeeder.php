<?php

use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Product::truncate();
        $filepath = base_path('database/seeds/data/product.sql');
        DB::unprepared(file_get_contents($filepath));
    }
}
