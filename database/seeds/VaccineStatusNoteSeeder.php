<?php

use App\Models\Vaccine\VaccineStatusNote;
use Illuminate\Database\Seeder;

class VaccineStatusNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        VaccineStatusNote::truncate();
        VaccineStatusNote::create([
            'id' => 1,
            'name' => 'Tujuan surat salah, seharusnya kepada Dinas Kesehatan Provinsi Jawa Barat',
        ]);
        VaccineStatusNote::create([
            'id' => 2,
            'name' => 'Detail permohonan di surat dan aplikasi tidak sama.',
        ]);
        VaccineStatusNote::create([
            'id' => 3,
            'name' => 'Barang yang dimohon sedang tidak tersedia',
        ]);
    }
}
