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

$factory->define(App\AllocationRequest::class, function (Faker $faker) {
    return [
        'letter_number' => $faker->numerify('SURAT/' . date('Y/m/d') . '/' . $faker->company . '/####'),
        'letter_date' => date('Y-m-d'),
        'type' => 'alkes',
        'applicant_name' => $faker->name,
        'applicant_position' => $faker->jobTitle . ' ' . $faker->company,
        'applicant_agency_id' =>  factory(App\MasterFaskes::class),
        'applicant_agency_name' => $faker->company,
        'distribution_description' => $faker->text,
        'letter_url' => $faker->url,
        'status' => 'success'
    ];
});
