<?php

use Illuminate\Database\Seeder;

class MasterFaskesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filepath = base_path('database/seeds/data/master-faskes.sql');
        DB::unprepared(file_get_contents($filepath));
    }
}
