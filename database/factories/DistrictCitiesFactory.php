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

$factory->define(App\Districtcities::class, function (Faker $faker) {
    return [
        'kemendagri_kabupaten_kode' => '32.01',
        'kemendagri_provinsi_nama' => 'JAWA BARAT',
        'kemendagri_provinsi_kode' => '32',
        'dinkes_kota_kode' => 1005,
        'kemendagri_kabupaten_nama' => 'KAB. BOGOR',
    ];
});
