<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\MasterFaskesType;
use App\MasterFaskes;
use App\AllocationMaterial;
use App\Districtcities;
use App\Subdistrict;
use App\VaccineRequest;
use App\Village;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
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
        factory(MasterFaskesType::class)->create(['id' => 1, 'name' => 'Rumah Sakit']);
        factory(MasterFaskesType::class)->create(['id' => 2, 'name' => 'Puskesmas']);
        factory(MasterFaskesType::class)->create(['id' => 3, 'name' => 'Klinik']);
        factory(MasterFaskesType::class)->create(['id' => 4, 'name' => 'Masyarakat Umum']);
        factory(MasterFaskesType::class)->create(['id' => 5, 'name' => 'Instansi Lainnya']);
        $this->districtcities = factory(Districtcities::class)->create();
        $this->subdistricts = factory(Subdistrict::class)->create([
            'kemendagri_kabupaten_kode' => $this->districtcities->kemendagri_kabupaten_kode,
            'kemendagri_kabupaten_nama' => $this->districtcities->kemendagri_kabupaten_nama,
        ]);
        $this->village = factory(Village::class)->create([
            'kemendagri_provinsi_kode' => '32',
            'kemendagri_provinsi_nama' => 'JAWA BARAT',
            'kemendagri_kabupaten_kode' => $this->subdistricts->kemendagri_kabupaten_kode,
            'kemendagri_kabupaten_nama' => $this->subdistricts->kemendagri_kabupaten_nama,
            'kemendagri_kecamatan_kode' => $this->subdistricts->kemendagri_kecamatan_kode,
            'kemendagri_kecamatan_nama' => $this->subdistricts->kemendagri_kecamatan_nama,
        ]);
        $this->faskes = factory(MasterFaskes::class)->create(['id_tipe_faskes' => rand(1, 3)]);
        $this->allocationMaterial = factory(AllocationMaterial::class)->create();
        $this->nonFaskes = factory(MasterFaskes::class)->create(['id_tipe_faskes' => rand(4, 5)]);
        $this->vaccineRequest = factory(VaccineRequest::class)->create([
            'agency_id' => $this->faskes->id,
            'agency_name' => $this->faskes->nama_faskes,
            'agency_type_id' => $this->faskes->id_tipe_faskes,
            'agency_village_id' => $this->village->kemendagri_desa_kode,
            'agency_district_id' => $this->village->kemendagri_kecamatan_kode,
            'agency_city_id' => $this->village->kemendagri_kabupaten_kode,
        ]);

        $this->logisticItems[] = [
            'product_id' => rand(),
            'quantity' => rand(),
            'unit' => 'PCS',
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
        ];
    }

    public function testGetVaccineRequestNoAuth()
    {
        $response = $this->json('GET', '/api/v1/vaccine-request');
        $response->assertUnauthorized();
    }

    public function testGetVaccineRequest()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-request');
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'agency_id',
                        'agency_name',
                        'agency_type_id',
                        'agency_type_name',
                        'agency_phone_number',
                        'agency_address',
                        'agency_village_id',
                        'agency_village_name',
                        'agency_district_id',
                        'agency_district_name',
                        'agency_city_id',
                        'agency_city_name',
                        'applicant_fullname',
                        'applicant_position',
                        'applicant_email',
                        'applicant_primary_phone_number',
                        'applicant_secondary_phone_number',
                        'applicant_file_url',
                        'letter_number',
                        'letter_file_url',
                        'status',
                        'created_at',
                        'updated_at',
                        'verified_at',
                        'verified_by',
                        'approved_at',
                        'approved_by',
                        'finalized_at',
                        'finalized_by',
                        'is_reference',
                        'is_completed',
                        'is_urgency'
                    ]
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next'
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total'
                ]
            ]);
    }

    public function testCreateVaccineRequestFailed()
    {
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-request');
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'master_faskes_id' => [],
                    'agency_type' => [],
                    'agency_name' => [],
                    'location_district_code' => [],
                    'location_subdistrict_code' => [],
                    'location_village_code' => [],
                    'applicant_name' => [],
                    'primary_phone_number' => [],
                    'logistic_request' => [],
                    'letter_file' => [],
                    'application_letter_number' => []
                ]
            ]);
    }

    public function testCreateVaccineRequestNoJobTitle()
    {
        Storage::fake('photos');
        Mail::fake();
        Notification::fake();

        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-request', [
            'master_faskes_id' => $this->faskes->id,
            'agency_type' => $this->faskes->id_tipe_faskes,
            'agency_name' => $this->faskes->nama_faskes,
            'location_village_code' => $this->village->kemendagri_desa_kode,
            'location_subdistrict_code' => $this->village->kemendagri_kecamatan_kode,
            'location_district_code' => $this->village->kemendagri_kabupaten_kode,
            'applicant_name' => $this->faker->name,
            'email' => $this->faker->email,
            'primary_phone_number' => $this->faker->numerify('081#########'),
            'secondary_phone_number' => $this->faker->numerify('081#########'),
            'logistic_request' => json_encode($this->logisticItems),
            'letter_file' => UploadedFile::fake()->image('letter_file.jpg'),
            'applicant_file' => UploadedFile::fake()->image('applicant_file.jpg'),
            'application_letter_number' => $this->faker->numerify('SURAT/' . date('Y/m/d') . '/####')
        ]);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testCreateVaccineRequest()
    {
        Storage::fake('photos');
        Mail::fake();
        Notification::fake();

        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-request', [
            'master_faskes_id' => $this->faskes->id,
            'agency_type' => $this->faskes->id_tipe_faskes,
            'agency_name' => $this->faskes->nama_faskes,
            'location_village_code' => $this->village->kemendagri_desa_kode,
            'location_subdistrict_code' => $this->village->kemendagri_kecamatan_kode,
            'location_district_code' => $this->village->kemendagri_kabupaten_kode,
            'applicant_name' => $this->faker->name,
            'applicants_office' => $this->faker->jobTitle,
            'email' => $this->faker->email,
            'primary_phone_number' => $this->faker->numerify('081#########'),
            'secondary_phone_number' => $this->faker->numerify('081#########'),
            'logistic_request' => json_encode($this->logisticItems),
            'letter_file' => UploadedFile::fake()->image('letter_file.jpg'),
            'applicant_file' => UploadedFile::fake()->image('applicant_file.jpg'),
            'application_letter_number' => $this->faker->numerify('SURAT/' . date('Y/m/d') . '/####')
        ]);
        $response->assertSuccessful();
    }
}
