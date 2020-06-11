<?php

use Illuminate\Database\Seeder;

class UpdateMasterFaskesTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\MasterFaskesType::where('name', '!=', 'Masyarakat Umum')->update(['non_public' => 1]);
        \App\MasterFaskesType::where('name', 'Instansi')->update(['name' => 'Instansi Lainnya']);
    }
}
