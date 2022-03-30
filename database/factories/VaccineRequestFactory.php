<?php

use App\Enums\VaccineRequestStatusEnum;
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

$factory->define(App\Models\Vaccine\VaccineRequest::class, function (Faker $faker) {
    return [
        'agency_id' =>  factory(App\Models\MedicalFacility::class),
        'agency_type_id' => factory(App\Models\MedicalFacilityType::class),
        'agency_name' => $faker->company,
        'agency_phone_number' => $faker->phoneNumber,
        'agency_address' => $faker->address,
        'agency_village_id' => $faker->numerify('32.##.##.####'),
        'agency_district_id' => $faker->numerify('32.##.##'),
        'agency_city_id' => $faker->numerify('32.##'),
        'applicant_fullname' => $faker->name,
        'applicant_position' => $faker->title,
        'applicant_email' => $faker->email,
        'applicant_primary_phone_number' => $faker->phoneNumber,
        'applicant_secondary_phone_number' => $faker->phoneNumber,
        'letter_number' => $faker->numerify('SURAT/' . date('Y/m/d') . '/####'),
        'letter_file_url' => $faker->url,
        'applicant_file_url' => $faker->url,
        'is_letter_file_final' => rand(0, 1),
        'delivery_plan_date' => $faker->date(),
        'vaccine_sprint_id' => null,
        'status' => VaccineRequestStatusEnum::not_verified(),
        'verified_at' => null,
        'verified_by' => null,
        'approved_by' => null,
        'approved_at' => null,
        'finalized_by' => null,
        'finalized_at' => null,
        'is_integrated' => 0,
        'integrated_by' => null,
        'integrated_at' => null,
        'delivered_by' => null,
        'delivered_at' => null,
        'is_urgency' => 0,
        'is_completed' => 0,
    ];
});
