<?php

use Illuminate\Database\Seeder;

class MasterFaskesTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\MasterFaskesType::truncate();
        $filepath = base_path('database/seeds/data/master_faskes_type.sql');
        DB::unprepared(file_get_contents($filepath));
    }
}
