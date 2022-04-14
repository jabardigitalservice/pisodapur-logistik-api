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

$factory->define(App\MasterFaskesType::class, function (Faker $faker) {
    return [
        'id' => 1,
        'name' => 'Rumah Sakit',
        'is_imported' => 0,
        'non_public' => 1
    ];
});
