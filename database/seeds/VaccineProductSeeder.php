<?php

use App\Enums\Vaccine\VaccineProductCategoryEnum;
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
        VaccineProduct::create(['name' => 'ASTRAZENECA', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine)]);
        VaccineProduct::create(['name' => 'CORONAVAC', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine)]);
        VaccineProduct::create(['name' => 'MODERNA', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine)]);
        VaccineProduct::create(['name' => 'PFIZER', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine)]);
        VaccineProduct::create(['name' => 'SINOPHARM', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine)]);
        VaccineProduct::create(['name' => 'COVAX', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine)]);
        VaccineProduct::create(['name' => 'VAKSIN LAINNYA', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine)]);

        VaccineProduct::create(['name' => 'ADS', 'category' => VaccineProductCategoryEnum::vaccine_support(), 'unit' => json_encode($unitVaccineSupport)]);
        VaccineProduct::create(['name' => 'ALCOHOL SWAB', 'category' => VaccineProductCategoryEnum::vaccine_support(), 'unit' => json_encode($unitVaccineSupport)]);
        VaccineProduct::create(['name' => 'SAFETY BOX', 'category' => VaccineProductCategoryEnum::vaccine_support(), 'unit' => json_encode($unitVaccineSupport)]);
        VaccineProduct::create(['name' => 'THERMAL SHIPPER PFIZER', 'category' => VaccineProductCategoryEnum::vaccine_support(), 'unit' => json_encode($unitVaccineSupport)]);
        VaccineProduct::create(['name' => 'PENDUKUNG VAKSIN LAINNYA', 'category' => VaccineProductCategoryEnum::vaccine_support(), 'unit' => json_encode($unitVaccineSupport)]);
    }
}
