<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\MasterFaskesType;
use App\MasterFaskes;
use App\AllocationMaterial;
use App\Districtcities;
use App\Enums\VaccineRequestStatusEnum;
use App\Subdistrict;
use App\Models\Vaccine\VaccineRequest;
use App\VaccineProductRequest;
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
            'category' => 'vaccine',
            'quantity' => rand(),
            'unit' => 'PCS',
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
        ];

        Storage::fake('photos');
        Mail::fake();
        Notification::fake();

        $this->vaccineRequestPayload = [
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
            'is_letter_file_final' => rand(0, 1),
            'application_letter_number' => $this->faker->numerify('SURAT/' . date('Y/m/d') . '/####')
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
                        'is_letter_file_final',
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

    public function testGetVaccineRequestById()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-request/' . $this->vaccineRequest->id);
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                  'id',
                  'agency_id',
                  'agency_type_id',
                  'agency_name',
                  'agency_phone_number',
                  'agency_address',
                  'agency_village_id',
                  'agency_district_id',
                  'agency_city_id',
                  'applicant_fullname',
                  'applicant_position',
                  'applicant_email',
                  'applicant_primary_phone_number',
                  'applicant_secondary_phone_number',
                  'letter_number',
                  'letter_file_url',
                  'is_letter_file_final',
                  'applicant_file_url',
                  'status',
                  'note',
                  'created_at',
                  'updated_at',
                  'created_by',
                  'is_completed',
                  'is_urgency',
                  'verified_at',
                  'verified_by',
                  'approved_at',
                  'approved_by',
                  'finalized_at',
                  'finalized_by',
                  'rejected_note',
                  'is_integrated',
                  'master_faskes' => [
                    'id',
                    'nama_faskes',
                    'is_reference',
                  ],
                  'master_faskes_type' => [
                    'id',
                    'name',
                  ],
                  'village' => [
                    'id',
                    'kemendagri_desa_nama',
                    'kemendagri_kabupaten_kode',
                    'kemendagri_provinsi_nama',
                    'kemendagri_desa_kode',
                    'kemendagri_provinsi_kode',
                    'kemendagri_kabupaten_nama',
                    'kemendagri_kecamatan_kode',
                    'kemendagri_kecamatan_nama',
                    'is_desa',
                    'created_at',
                    'updated_at',
                  ],
                  'outbounds' => [],
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
        $vaccineRequestPayload = $this->vaccineRequestPayload;
        unset($vaccineRequestPayload['applicants_office']);
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-request', $vaccineRequestPayload);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testCreateVaccineRequest()
    {
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-request', $this->vaccineRequestPayload);
        $response->assertSuccessful();
    }

    public function testVerifiedStatusVaccineRequestById()
    {
        $response = $this->actingAs($this->admin, 'api')->json('PUT', '/api/v1/vaccine-request/' . $this->vaccineRequest->id, [
            'status' => VaccineRequestStatusEnum::verified()
        ]);
        $response->assertSuccessful();
    }

    public function testApproveStatusVaccineRequestById()
    {
        $response = $this->actingAs($this->admin, 'api')->json('PUT', '/api/v1/vaccine-request/' . $this->vaccineRequest->id, [
            'status' => VaccineRequestStatusEnum::approved()
        ]);
        $response->assertSuccessful();
    }

    public function testRejectStatusVaccineRequestById()
    {
        $response = $this->actingAs($this->admin, 'api')->json('PUT', '/api/v1/vaccine-request/' . $this->vaccineRequest->id, [
            'status' => VaccineRequestStatusEnum::rejected(),
            'note' => $this->faker->text
        ]);
        $response->assertSuccessful();
    }

    public function testCreateVaccineRequestFailVaccineProduct()
    {
        $payload = $this->vaccineRequestPayload;

        $logisticItems[] = [
            'product_id' => '',
            'category' => 'vaccinea',
            'quantity' => 'a',
            'unit' => null,
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
        ];
        $payload['logistic_request'] = json_encode($logisticItems);

        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-request', $payload);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testGetProductRequest()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-product-request', [
            'vaccine_request_id' => $this->vaccineRequest->id
        ]);
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
              ]);
    }

    public function testUpdateProductRequest()
    {
        $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-request', $this->vaccineRequestPayload);

        $vaccineProductRequest = VaccineProductRequest::first()->toArray();
        $vaccineProductRequest['quantity'] = rand(100, 1000);
        $response = $this->actingAs($this->admin, 'api')->json('PUT', '/api/v1/vaccine-product-request/' . $vaccineProductRequest['id'], $vaccineProductRequest);
        $response->assertSuccessful();
    }
}
