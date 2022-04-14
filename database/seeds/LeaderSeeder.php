<?php

use App\Models\Leader;
use Illuminate\Database\Seeder;

class LeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Leader::truncate();
        Leader::create([
            'fullname' => 'dr. R. Nina Susana Dewi, Sp.PK(K)., M.Kes., MMRS',
            'role' => 'Kepala Dinkes Provinsi Jawa Barat',
            'phase' => 'finalized'
        ]);
    }
}
