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

$factory->define(App\Village::class, function (Faker $faker) {
    return [
        'kemendagri_kabupaten_kode' => '32.01',
        'kemendagri_kabupaten_nama' => 'KAB. BOGOR',
        'kemendagri_provinsi_kode' => '32',
        'kemendagri_provinsi_nama' => 'JAWA BARAT',
        'kemendagri_kecamatan_kode' => '32.01.01',
        'kemendagri_kecamatan_nama' => 'Cibinong',
        'kemendagri_desa_kode' => '32.01.01.1001',
        'kemendagri_desa_nama' => 'Pondok Rajeg',
        'is_desa' => 'true',
    ];
});
