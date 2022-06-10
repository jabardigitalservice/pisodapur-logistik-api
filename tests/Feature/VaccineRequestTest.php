<?php

namespace Tests\Feature;

use Tests\TestCase;
use DB;
use App\User;
use App\AllocationMaterial;
use App\Districtcities;
use App\Enums\Vaccine\VaccineProductCategoryEnum;
use App\Enums\VaccineProductRequestStatusEnum;
use App\Enums\VaccineRequestStatusEnum;
use App\Models\MedicalFacility;
use App\Models\MedicalFacilityType;
use App\Subdistrict;
use App\Models\Vaccine\VaccineRequest;
use App\VaccineProductRequest;
use App\Village;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
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
        $this->allocationMaterial = factory(AllocationMaterial::class)->create();

        $this->medicalFacility = factory(MedicalFacility::class)->create();
        $this->vaccineRequest = factory(VaccineRequest::class)->create([
            'agency_id' => $this->medicalFacility->id,
            'agency_name' => $this->medicalFacility->name,
            'agency_type_id' => $this->medicalFacility->medical_facility_type_id,
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

        $this->logisticItems[] = [
            'product_id' => rand(),
            'category' => 'vaccine_support',
            'quantity' => rand(),
            'unit' => 'PCS',
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
        ];

        Storage::fake('photos');
        Notification::fake();

        $this->vaccineRequestPayload = [
            'master_faskes_id' => $this->medicalFacility->id,
            'agency_name' => $this->medicalFacility->name,
            'agency_type' => $this->medicalFacility->medical_facility_type_id,
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

        $this->statusNoteChoice = [
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
            [
                'id' => 3,
            ]
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

    public function testGetVaccineRequestFilterByIsLetterFileFinal()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-request', [
            'is_letter_file_final' => rand(0, 1)
        ]);
        $response
            ->assertSuccessful();
    }

    public function testGetVaccineRequestFilterByIsLetterFileFinalFailed()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-request', [
            'is_letter_file_final' => 2
        ]);
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                "message",
                "errors" => [
                  "is_letter_file_final" => []
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
                    'is_completed',
                    'is_urgency'
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

    public function testCreateVaccineRequestOtherInstance()
    {
        MedicalFacilityType::insert([
            'id' => 99,
            'name' => 'Instansi Lainnya'
        ]);

        $vaccineRequestPayload = $this->vaccineRequestPayload;

        $vaccineRequestPayload['master_faskes_id'] = null;
        $vaccineRequestPayload['agency_type'] = 99;
        $vaccineRequestPayload['agency_name'] = $this->faker->company;
        $vaccineRequestPayload['phone_number'] = $this->faker->phoneNumber;
        $vaccineRequestPayload['location_address'] = $this->faker->address;

        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-request', $vaccineRequestPayload);
        $response->assertSuccessful();
    }

    public function testCreateVaccineRequestWithNoteProduct()
    {
        $logisticItems[] = [
            'product_id' => rand(),
            'category' => 'vaccine',
            'quantity' => rand(),
            'unit' => 'PCS',
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
            'note' => $this->faker->text,
        ];
        $vaccineRequestPayload = $this->vaccineRequestPayload;
        $vaccineRequestPayload['logistic_request'] = json_encode($logisticItems);

        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-request', $vaccineRequestPayload);
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                "status",
                "message",
                "data" => [
                "need" => [
                    [
                        "note",
                    ]
                ]
                ]
            ]);
    }

    public function testVerifiedStatusVaccineRequestByIdStatusOnly()
    {
        $response = $this->actingAs($this->admin, 'api')->json('PUT', '/api/v1/vaccine-request/' . $this->vaccineRequest->id, [
            'status' => VaccineRequestStatusEnum::verified()
        ]);
        $response->assertSuccessful();
    }

    public function testVerifiedStatusVaccineRequestByIdWithNote()
    {
        $response = $this->actingAs($this->admin, 'api')->json('PUT', '/api/v1/vaccine-request/' . $this->vaccineRequest->id, [
            'status' => VaccineRequestStatusEnum::verified(),
            'vaccine_status_note' => $this->statusNoteChoice,
            'note' => $this->faker->text
        ]);
        $response->assertSuccessful();
    }

    public function testApproveStatusVaccineRequestByIdStatusOnly()
    {
        $response = $this->actingAs($this->admin, 'api')->json('PUT', '/api/v1/vaccine-request/' . $this->vaccineRequest->id, [
            'status' => VaccineRequestStatusEnum::approved()
        ]);
        $response->assertSuccessful();
    }

    public function testApproveStatusVaccineRequestByIdWithNote()
    {
        $response = $this->actingAs($this->admin, 'api')->json('PUT', '/api/v1/vaccine-request/' . $this->vaccineRequest->id, [
            'status' => VaccineRequestStatusEnum::approved(),
            'vaccine_status_note' => $this->statusNoteChoice,
            'note' => $this->faker->text
        ]);
        $response->assertSuccessful();
    }

    public function testFinalizedStatusVaccineRequestById()
    {
        $response = $this->actingAs($this->admin, 'api')->json('PUT', '/api/v1/vaccine-request/' . $this->vaccineRequest->id, [
            'status' => VaccineRequestStatusEnum::finalized(),
            'delivery_plan_date' => $this->faker->date()
        ]);
        $response->assertSuccessful();
    }

    public function testIntegratedStatusVaccineRequestById()
    {
        $response = $this->actingAs($this->admin, 'api')->json('PUT', '/api/v1/vaccine-request/' . $this->vaccineRequest->id, [
            'status' => VaccineRequestStatusEnum::integrated()
        ]);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR); // This is because need integration with third party.
    }

    public function testBookedStatusVaccineRequestById()
    {
        $admin = factory(User::class)->create([
            'username'    => 'username@example.net',
            'password' => bcrypt('secret'),
        ]);

        $login = $this->post('/api/v1/login', [
            'username'    => 'username@example.net',
            'password' => 'secret',
        ]);

        $responseData = $login->json();
        $token = $responseData['data']['token'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('PUT', '/api/v1/vaccine-poslog/' . $this->vaccineRequest->id, [
                'status' => VaccineRequestStatusEnum::booked()
            ]);
        $response->assertSuccessful();
    }

    public function testDoStatusVaccineRequestById()
    {
        $admin = factory(User::class)->create([
            'username'    => 'username@example.net',
            'password' => bcrypt('secret'),
        ]);

        $login = $this->post('/api/v1/login', [
            'username'    => 'username@example.net',
            'password' => 'secret',
        ]);

        $responseData = $login->json();
        $token = $responseData['data']['token'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('PUT', '/api/v1/vaccine-poslog/' . $this->vaccineRequest->id, [
            'status' => VaccineRequestStatusEnum::do()
        ]);
        $response->assertSuccessful();
    }

    public function testIntransitStatusVaccineRequestById()
    {
        $admin = factory(User::class)->create([
            'username'    => 'username@example.net',
            'password' => bcrypt('secret'),
        ]);

        $login = $this->post('/api/v1/login', [
            'username'    => 'username@example.net',
            'password' => 'secret',
        ]);

        $responseData = $login->json();
        $token = $responseData['data']['token'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('PUT', '/api/v1/vaccine-poslog/' . $this->vaccineRequest->id, [
            'status' => VaccineRequestStatusEnum::intransit()
        ]);
        $response->assertSuccessful();
    }

    public function testDeliveredStatusVaccineRequestById()
    {
        $response = $this->actingAs($this->admin, 'api')->json('PUT', '/api/v1/vaccine-request/' . $this->vaccineRequest->id, [
            'status' => VaccineRequestStatusEnum::delivered()
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
        VaccineProductRequest::create([
            'vaccine_request_id' => $this->vaccineRequest->id,
            'product_id' => rand(),
            'category' => 'vaccine',
            'quantity' => rand(),
            'unit' => 'PCS',
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
        ]);
        VaccineProductRequest::create([
            'vaccine_request_id' => $this->vaccineRequest->id,
            'product_id' => rand(),
            'category' => 'vaccine_support',
            'quantity' => rand(),
            'unit' => 'PCS',
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
        ]);
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-product-request', [
            'vaccine_request_id' => $this->vaccineRequest->id
        ]);
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    [
                        "id",
                        "vaccine_request_id",
                        "product_id",
                        "product_name",
                        "quantity",
                        "unit",
                        "product_status",
                        "category",
                        "usage",
                        "description",
                        "note",
                        "created_at",
                        "updated_at"
                    ]
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total'
                ],
              ]);
    }

    public function testGetProductRequestFilterByStatusRecommendation()
    {
        VaccineProductRequest::create([
            'vaccine_request_id' => $this->vaccineRequest->id,
            'product_id' => rand(),
            'category' => 'vaccine',
            'quantity' => rand(),
            'unit' => 'PCS',
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
        ]);
        VaccineProductRequest::create([
            'vaccine_request_id' => $this->vaccineRequest->id,
            'product_id' => rand(),
            'category' => 'vaccine_support',
            'quantity' => rand(),
            'unit' => 'PCS',
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
        ]);
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-product-request', [
            'vaccine_request_id' => $this->vaccineRequest->id,
            'category' => 'vaccine',
            'status' => 'recommendation'
        ]);
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    [
                        "id",
                        "vaccine_request_id",
                        "product_id",
                        "product_name",
                        "quantity",
                        "unit",
                        "product_status",
                        "recommendation_note",
                        "category",
                        "usage",
                        "description",
                        "note",
                        "reason",
                        "file_url",
                        "created_at",
                        "updated_at"
                    ]
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total'
                ],
              ]);
    }

    public function testGetProductRequestFilterByStatusFinalization()
    {
        VaccineProductRequest::create([
            'vaccine_request_id' => $this->vaccineRequest->id,
            'product_id' => rand(),
            'category' => 'vaccine',
            'quantity' => rand(),
            'unit' => 'PCS',
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
        ]);
        VaccineProductRequest::create([
            'vaccine_request_id' => $this->vaccineRequest->id,
            'product_id' => rand(),
            'category' => 'vaccine_support',
            'quantity' => rand(),
            'unit' => 'PCS',
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
        ]);
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-product-request', [
            'vaccine_request_id' => $this->vaccineRequest->id,
            'category' => 'vaccine_support',
            'status' => 'finalization'
        ]);
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    [
                        "id",
                        "vaccine_request_id",
                        "product_id",
                        "product_name",
                        "quantity",
                        "unit",
                        "product_status",
                        "recommendation_note",
                        "category",
                        "usage",
                        "description",
                        "note",
                        "reason",
                        "file_url",
                        "created_at",
                        "updated_at"
                    ]
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total'
                ],
              ]);
    }

    public function testGetVaccineProductRequestById()
    {
        $vaccine = VaccineProductRequest::create([
            'vaccine_request_id' => $this->vaccineRequest->id,
            'product_id' => rand(),
            'category' => 'vaccine',
            'quantity' => rand(),
            'unit' => 'PCS',
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
        ]);
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-product-request/'. $vaccine->id);
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'request' => [],
                    'recommendation' => [],
                    'finalization' => [],
                    'delivery_plan' => [],
                ]
            ]);
    }

    public function testGetVaccineSupportProductRequestById()
    {
        $vaccineSupport = VaccineProductRequest::create([
            'vaccine_request_id' => $this->vaccineRequest->id,
            'product_id' => rand(),
            'category' => 'vaccine_support',
            'quantity' => rand(),
            'unit' => 'PCS',
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
        ]);
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-product-request/'. $vaccineSupport->id);
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'request' => [],
                    'recommendation' => [],
                    'finalization' => [],
                    'delivery_plan' => [],
                ]
            ]);
    }

    public function testUpdateProductRequest()
    {
        $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-request', $this->vaccineRequestPayload);

        $vaccineProductRequest = VaccineProductRequest::first()->toArray();
        $vaccineProductRequest['quantity'] = rand(100, 1000);
        $vaccineProductRequest['recommendaton_note'] = $this->faker->text();
        $response = $this->actingAs($this->admin, 'api')->json('PUT', '/api/v1/vaccine-product-request/' . $vaccineProductRequest['id'], $vaccineProductRequest);
        $response->assertSuccessful();
    }

    public function testCreateVaccineProductRequestById()
    {
        Storage::fake('photos');
        $vaccineSupport = [
            'vaccine_request_id' => $this->vaccineRequest->id,
            'recommendation_product_id' => $this->allocationMaterial->material_id,
            'recommendation_product_name' => $this->allocationMaterial->material_name,
            'category' => VaccineProductCategoryEnum::vaccine(),
            'recommendation_quantity' => rand(),
            'recommendation_date' => $this->faker->date(),
            'recommendation_UoM' => $this->allocationMaterial->UoM,
            'recommendation_status' => VaccineProductRequestStatusEnum::urgent(),
            'usage' => $this->faker->text,
            'recommendation_reason' => $this->faker->text,
            'recommendation_file' => UploadedFile::fake()->image('file_permintaan_tambahan.jpg')

        ];
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-product-request', $vaccineSupport);
        $response
            ->assertSuccessful();
    }
}
