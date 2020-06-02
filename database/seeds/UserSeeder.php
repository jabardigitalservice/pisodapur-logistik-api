<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\User::create([
            'name'                => 'Pikobar Donasi',
            'username'            => 'pikobardonasi',
            'password'            => Hash::make('pass890'),
            'email'               => 'pikobardonasi@example.com',
            'roles'               => 'dinkesprov',
            'code_district_city'  => '32.73',
            'name_district_city'  => 'KOTA BANDUNG',
        ]);
    }
}
