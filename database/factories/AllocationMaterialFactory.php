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

$factory->define(App\AllocationMaterial::class, function (Faker $faker) {
    return [
        'matg_id' => 'VAKSIN',
        'material_id' => $faker->numerify('MAT-' . substr($faker->name, 1) . '##' . substr($faker->name, 3) . '##'),
        'material_name' => $faker->company,
        'type' => 'vaccine',
        'UoM' => $faker->stateAbbr(),
        'soh_location' => $faker->citySuffix,
        'soh_location_name' => $faker->city,
        'stock_ok' => rand(0, 1000),
        'stock_nok' => rand(0, 1000),
        'booked_stock' => rand(0, 1000)
    ];
});
