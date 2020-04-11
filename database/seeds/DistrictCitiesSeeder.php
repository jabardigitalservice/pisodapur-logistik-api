<?php

use Illuminate\Database\Seeder;

class DistrictCitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filepath = base_path('database/seeds/data/districtcities.sql');
        DB::unprepared(file_get_contents($filepath));
    }
}
