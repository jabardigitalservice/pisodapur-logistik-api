<?php

use App\Models\MedicalFacilityType;
use Illuminate\Database\Seeder;

class MedicalFacilityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MedicalFacilityType::create(['id' => 1, 'name' => 'Dinas Kesehatan']);
        MedicalFacilityType::create(['id' => 2, 'name' => 'TNI']);
        MedicalFacilityType::create(['id' => 3, 'name' => 'POLRI']);
        MedicalFacilityType::create(['id' => 99, 'name' => 'Instansi Lainnya']);
    }
}
