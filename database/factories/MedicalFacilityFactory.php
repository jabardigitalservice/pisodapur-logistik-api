<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\MedicalFacility::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'medical_facility_type_id' => factory(App\Models\MedicalFacilityType::class),
        'city_id' => factory(App\Districtcities::class),
        'district_id' => factory(App\Subdistrict::class),
        'village_id' => factory(App\Village::class),
        'address' => $faker->address,
        'phone' => $faker->phoneNumber
    ];
});
