<?php

use Illuminate\Database\Seeder;
use App\MasterFaskesType;

class MasterFaskesTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => 1,
                'name' => 'Rumah Sakit'
            ],
            [
                'id' => 2,
                'name' => 'Puskesmas'
            ]
        ];
        foreach ($data as $key => $value) {
            MasterFaskesType::create($value);
        }
    }
}
