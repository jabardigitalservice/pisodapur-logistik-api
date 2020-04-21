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
            ],
            [
                'id' => 3,
                'name' => 'Klinik'
            ]
        ];
        foreach ($data as $key => $value) {
            $masterFaskesType = MasterFaskesType::find($value['id']);
            if (!$masterFaskesType) {
                MasterFaskesType::create($value);
            }
        }
    }
}
