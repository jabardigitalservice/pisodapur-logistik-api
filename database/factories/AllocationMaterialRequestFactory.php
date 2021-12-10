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

$factory->define(App\AllocationMaterialRequest::class, function (Faker $faker) {
    return [
        'allocation_request_id' => factory(App\AllocationRequest::class),
        'allocation_distribution_request_id' => factory(App\AllocationDistributionRequest::class),
        'matg_id' => 'VAKSIN',
        'material_id' => factory(App\AllocationMaterial::class),
        'material_name' => $faker->company,
        'qty' => rand(),
        'UoM' => 'PCS'
    ];
});
