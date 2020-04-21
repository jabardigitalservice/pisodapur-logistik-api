<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DistrictCitiesSeeder::class);
        $this->call(SubdistrictSeeder::class);
        $this->call(MasterFaskesTypeSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(ProductUnitSeeder::class);
        $this->call(MasterUnitSeeder::class);
        $this->call(MasterFaskesUpdateSeeder::class);
    }
}
