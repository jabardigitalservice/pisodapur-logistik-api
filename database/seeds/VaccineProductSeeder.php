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

        $purposes = [
            ['id' => 'DOSIS 1', 'name' => 'DOSIS 1'],
            ['id' => 'DOSIS 2', 'name' => 'DOSIS 2'],
            ['id' => 'DOSIS 3', 'name' => 'DOSIS 3'],
            ['id' => 'LAINNYA', 'name' => 'LAINNYA'],
        ];

        VaccineProduct::create(['name' => 'ASTRAZENECA', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine), 'purposes' => json_encode($purposes)]);
        VaccineProduct::create(['name' => 'CORONAVAC', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine), 'purposes' => json_encode($purposes)]);
        VaccineProduct::create(['name' => 'MODERNA', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine), 'purposes' => json_encode($purposes)]);
        VaccineProduct::create(['name' => 'PFIZER', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine), 'purposes' => json_encode($purposes)]);
        VaccineProduct::create(['name' => 'SINOPHARM', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine), 'purposes' => json_encode($purposes)]);
        VaccineProduct::create(['name' => 'COVAX', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine), 'purposes' => json_encode($purposes)]);
        VaccineProduct::create(['name' => 'VAKSIN LAINNYA', 'category' => VaccineProductCategoryEnum::vaccine(), 'unit' => json_encode($unitForVaccine), 'purposes' => json_encode($purposes)]);

        VaccineProduct::create(['name' => 'ADS', 'category' => VaccineProductCategoryEnum::vaccine_support(), 'unit' => json_encode($unitVaccineSupport), 'purposes' => json_encode($purposes)]);
        VaccineProduct::create(['name' => 'ALCOHOL SWAB', 'category' => VaccineProductCategoryEnum::vaccine_support(), 'unit' => json_encode($unitVaccineSupport), 'purposes' => json_encode($purposes)]);
        VaccineProduct::create(['name' => 'SAFETY BOX', 'category' => VaccineProductCategoryEnum::vaccine_support(), 'unit' => json_encode($unitVaccineSupport), 'purposes' => json_encode($purposes)]);
        VaccineProduct::create(['name' => 'THERMAL SHIPPER PFIZER', 'category' => VaccineProductCategoryEnum::vaccine_support(), 'unit' => json_encode($unitVaccineSupport), 'purposes' => json_encode($purposes)]);
        VaccineProduct::create(['name' => 'PENDUKUNG VAKSIN LAINNYA', 'category' => VaccineProductCategoryEnum::vaccine_support(), 'unit' => json_encode($unitVaccineSupport), 'purposes' => json_encode($purposes)]);
    }
}
