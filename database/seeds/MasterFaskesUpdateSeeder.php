<?php

use Illuminate\Database\Seeder;

class MasterFaskesUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filepath = base_path('database/seeds/data/master_faskes_update.sql');
        DB::unprepared(file_get_contents($filepath));
    }
}
