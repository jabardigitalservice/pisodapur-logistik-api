<?php

namespace Tests\Feature;

use App\MasterFaskes;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class VaccineRequestTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = factory(User::class)->create();
        $this->faskes = factory(MasterFaskes::class)->create();
        $this->nonFaskes = factory(MasterFaskes::class)->create(['id_tipe_faskes' => rand(4, 5)]);
    }

    public function testStoreVaccineRequestFaskes()
    {
        Storage::fake('photos');
        Mail::fake();
        Notification::fake();

        $logisticItems[] = [
            'usage' => $this->faker->text,
            'product_id' => rand(1,200),
            'description' => $this->faker->text,
            'quantity' => rand(1,99999),
            'unit' => 'PCS'
        ];

        $response = $this->json('POST', '/api/v1/vaccine-request', [
            'agency_type' => $this->faskes->id_tipe_faskes,
            'agency_name' => $this->faskes->nama_faskes,
            'phone_number' => $this->faker->numerify('081#########'),
            'location_district_code' => $this->faker->numerify('##.##'),
            'location_subdistrict_code' => $this->faker->numerify('##.##.##'),
            'location_village_code' => $this->faker->numerify('##.##.##.####'),
            'location_address' => $this->faker->address,
            'applicant_name' => $this->faker->name,
            'applicants_office' => $this->faker->jobTitle . ' ' . $this->faskes->nama_faskes,
            'email' => $this->faker->email,
            'primary_phone_number' => $this->faker->numerify('081#########'),
            'secondary_phone_number' => $this->faker->numerify('081#########'),
            'master_faskes_id' => $this->faskes->id,
            'logistic_request' => json_encode($logisticItems),
            'letter_file' => UploadedFile::fake()->image('letter_file.png'),
            'applicant_file' => UploadedFile::fake()->image('applicant_file.png'),
            'application_letter_number' => $this->faker->numerify('SURAT/' . date('Y/m/d') . '/' . $this->faker->company . '/####'),
        ]);
        dd($response);
        $response->assertSuccessful();
    }

    public function testStoreVaccineRequestNonFaskes()
    {
        Storage::fake('photos');
        Mail::fake();
        Notification::fake();

        $logisticItems[] = [
            'usage' => $this->faker->text,
            'priority' => 'Menengah',
            'product_id' => rand(1,200),
            'description' => $this->faker->text,
            'quantity' => rand(1,99999),
            'unit' => 'PCS'
        ];

        $response = $this->json('POST', '/api/v1/vaccine-request', [
            'agency_type' => $this->nonFaskes->id_tipe_faskes,
            'agency_name' => $this->nonFaskes->nama_faskes,
            'phone_number' => $this->faker->numerify('081#########'),
            'location_district_code' => $this->faker->numerify('##.##'),
            'location_subdistrict_code' => $this->faker->numerify('##.##.##'),
            'location_village_code' => $this->faker->numerify('##.##.##.####'),
            'location_address' => $this->faker->address,
            'applicant_name' => $this->faker->name,
            'applicants_office' => $this->faker->jobTitle . ' ' . $this->nonFaskes->nama_faskes,
            'email' => $this->faker->email,
            'primary_phone_number' => $this->faker->numerify('081#########'),
            'secondary_phone_number' => $this->faker->numerify('081#########'),
            'master_faskes_id' => $this->nonFaskes->id,
            'logistic_request' => json_encode($logisticItems),
            'letter_file' => UploadedFile::fake()->image('letter_file.jpg'),
            'applicant_file' => UploadedFile::fake()->image('applicant_file.jpg'),
            'application_letter_number' => $this->faker->numerify('SURAT/' . date('Y/m/d') . '/' . $this->faker->company . '/####'),
            'total_covid_patients' => rand(0, 100),
            'total_isolation_room' => rand(0, 100),
            'total_bedroom' => rand(0, 100),
            'total_health_worker' => rand(0, 100)
        ]);
        $response->assertSuccessful();
    }
}
