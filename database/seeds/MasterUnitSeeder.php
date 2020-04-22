<?php

use Illuminate\Database\Seeder;

class MasterUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filepath = base_path('database/seeds/data/master_unit.sql');
        DB::unprepared(file_get_contents($filepath));
    }
}
