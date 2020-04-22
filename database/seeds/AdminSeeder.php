<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\User::create([
            'name'                => 'Dinkes Provinsi Jawa Barat',
            'username'            => 'dinkesprov',
            'password'            => Hash::make('asdf890'),
            'email'               => 'dinkesprov@example.com',
            'roles'               => 'dinkesprov',
            'code_district_city'  => '32.73', 
            'name_district_city'  => 'KOTA BANDUNG',
        ]);

        \App\User::create([
            'name'                => 'Dinkes Kota Bandung',
            'username'            => 'dinkeskotabandung',
            'password'            => Hash::make('asdf890'),
            'email'               => 'dinkeskota@example.com',
            'roles'               => 'dinkeskota',
            'code_district_city'  => '32.73', 
            'name_district_city'  => 'KOTA BANDUNG',
        ]);
    }
}
