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

$factory->define(App\AllocationDistributionRequest::class, function (Faker $faker) {
    return [
        'allocation_request_id' => factory(App\AllocationRequest::class),
        'agency_id' => factory(App\MasterFaskes::class),
        'agency_name' => $faker->company,
        'distribution_plan_date' => date('Y-m-d')
    ];
});
