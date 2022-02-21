<?php

use App\Models\Vaccine\VaccineProduct;
use Illuminate\Database\Seeder;

class VaccineProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        VaccineProduct::truncate();
        $unitForVaccine = [
            ['id' => 'VIAL', 'name' => 'VIAL'],
            ['id' => 'DOSIS', 'name' => 'DOSIS'],
        ];

        $unitVaccineSupport = [
            ['id' => 'PCS', 'name' => 'PCS']
        ];
        VaccineProduct::create(['name' => 'ASTRAZENECA', 'category' => VaccineProduct::CATEGORY_VACCINE, 'unit' => json_encode($unitForVaccine)]);
        VaccineProduct::create(['name' => 'CORONAVAC', 'category' => VaccineProduct::CATEGORY_VACCINE, 'unit' => json_encode($unitForVaccine)]);
        VaccineProduct::create(['name' => 'MODERNA', 'category' => VaccineProduct::CATEGORY_VACCINE, 'unit' => json_encode($unitForVaccine)]);
        VaccineProduct::create(['name' => 'PFIZER', 'category' => VaccineProduct::CATEGORY_VACCINE, 'unit' => json_encode($unitForVaccine)]);
        VaccineProduct::create(['name' => 'SINOPHARM', 'category' => VaccineProduct::CATEGORY_VACCINE, 'unit' => json_encode($unitForVaccine)]);
        VaccineProduct::create(['name' => 'COVAX', 'category' => VaccineProduct::CATEGORY_VACCINE, 'unit' => json_encode($unitForVaccine)]);
        VaccineProduct::create(['name' => 'VAKSIN LAINNYA', 'category' => VaccineProduct::CATEGORY_VACCINE, 'unit' => json_encode($unitForVaccine)]);

        VaccineProduct::create(['name' => 'ADS', 'category' => VaccineProduct::CATEGORY_VACCINE_SUPPORT, 'unit' => json_encode($unitVaccineSupport)]);
        VaccineProduct::create(['name' => 'ALCOHOL SWAB', 'category' => VaccineProduct::CATEGORY_VACCINE_SUPPORT, 'unit' => json_encode($unitVaccineSupport)]);
        VaccineProduct::create(['name' => 'SAFETY BOX', 'category' => VaccineProduct::CATEGORY_VACCINE_SUPPORT, 'unit' => json_encode($unitVaccineSupport)]);
        VaccineProduct::create(['name' => 'THERMAL SHIPPER PFIZER', 'category' => VaccineProduct::CATEGORY_VACCINE_SUPPORT, 'unit' => json_encode($unitVaccineSupport)]);
        VaccineProduct::create(['name' => 'PENDUKUNG VAKSIN LAINNYA', 'category' => VaccineProduct::CATEGORY_VACCINE_SUPPORT, 'unit' => json_encode($unitVaccineSupport)]);
    }
}
