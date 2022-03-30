<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\MasterFaskes;
use App\AllocationMaterial;
use App\AllocationDistributionRequest;
use App\AllocationMaterialRequest;
use App\AllocationRequest;
use App\Product;
use App\Enums\AllocationRequestStatusEnum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class AllocationVaccineLogisticRequestTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = factory(User::class)->create();
        $this->faskes = factory(MasterFaskes::class)->create();
        $this->allocationMaterial = factory(AllocationMaterial::class)->create();
        $this->nonFaskes = factory(MasterFaskes::class)->create(['id_tipe_faskes' => rand(4, 5)]);
        $this->allocationRequest = factory(AllocationRequest::class)->create([
            'applicant_agency_id' => $this->faskes->id,
            'applicant_agency_name' => $this->faskes->nama_faskes,
            'type' => 'vaccine',
        ]);

        $this->product = factory(Product::class)->create([
            'material_group' => 'VAKSIN'
        ]);

        $this->allocationDistributionRequest = factory(AllocationDistributionRequest::class)->create([
            'allocation_request_id' => $this->allocationRequest->id,
            'agency_id' => $this->faskes->id,
            'agency_name' => $this->faskes->nama_faskes
        ]);

        $this->allocationMaterialRequest = factory(AllocationMaterialRequest::class)->create([
            'allocation_request_id' => $this->allocationRequest->id,
            'allocation_distribution_request_id' => $this->allocationDistributionRequest->id,
            'matg_id' => $this->allocationMaterial->matg_id,
            'material_id' => $this->allocationMaterial->material_id,
            'material_name' => $this->allocationMaterial->material_name,
            'qty' => rand(),
            'UoM' => $this->allocationMaterial->UoM
        ]);
    }

    public function testGetAllocationVaccineLogisticRequestNoAuth()
    {
        $response = $this->json('GET', '/api/v1/allocation-vaccine-request');
        $response->assertUnauthorized();
    }

    public function testGetAllocationVaccineLogisticRequestByAllocationRequestNoAuth()
    {
        $allocationRequest = $this->allocationRequest->id;
        $response = $this->json('GET', '/api/v1/allocation-vaccine-request/' . $allocationRequest);
        $response->assertUnauthorized();
    }

    public function testGetAllocationVaccineLogisticRequest()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/allocation-vaccine-request', [
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d'),
            'search' => 'SURAT',
            'status' => AllocationRequestStatusEnum::success(),
        ]);
        $response->assertSuccessful();
    }

    public function testGetAllocationDistributionRequest()
    {
        $allocationRequest = $this->allocationRequest->id;
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/allocation-distribution-vaccine-request', [
            'allocation_request_id' => $allocationRequest,
            'search' => 'RSUD',
        ]);
        $response->assertSuccessful();
    }

    public function testGetAllocationVaccineLogisticRequestById()
    {
        $allocationRequest = $this->allocationRequest->id;
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/allocation-vaccine-request/' . $allocationRequest);
        $response->assertSuccessful();
    }

    public function testGetAllocationMaterial()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-material');
        $response->assertSuccessful();
    }

    public function testGetAllocationMaterialWhereMaterialName()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-material', [
            'material_name' => 'CORONAVAC'
        ]);
        $response->assertSuccessful();
    }

    public function testGetAllocationMaterialByMatgId()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-material', [
            'matg_id' => 'VAKSIN'
        ]);
        $response->assertSuccessful();
    }

    public function testGetAllocationMaterialByMaterialId()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-material', [
            'material_id' => $this->allocationMaterial->material_id,
        ]);
        $response->assertSuccessful();
    }

    public function testGetAllocationRequestStatistic()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/allocation-vaccine-request-statistic');
        $response->assertSuccessful();
    }

    public function testGetAllocationMaterialById()
    {
        $data = AllocationMaterial::first();
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-material/' . $data->material_id);
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'matg_id',
                    'material_id',
                    'material_name',
                    'type',
                    'UoM',
                    'created_at',
                    'updated_at',
                    'soh_location',
                    'soh_location_name',
                    'stock_ok',
                    'stock_nok',
                    'booked_stock',
                    'current_stock',
                    'current_stock_formatted',
                ]
            ]);
    }

    public function testAllocationVaccineImportInvalidId()
    {
        $name = 'exampleAllocationVaccineImport.xlsx';
        $path = resource_path() . '/' . $name;
        $file = new UploadedFile($path, $name, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/allocation-vaccine-import', [
            'file' => $file
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testStoreAllocationRequestNoPayload()
    {
        $payload = [];

        $this
            ->actingAs($this->admin, 'api')->json('POST', '/api/v1/allocation-vaccine-request', $payload)
            ->assertStatus(422);
    }

    public function testStoreAllocationRequestNoInstanceList()
    {
        $payload = [
            'letter_number' => $this->faker->numerify('SURAT/' . date('Y/m/d') . '/' . $this->faker->company . '/####'),
            'letter_date' => date('Y-m-d'),
            'applicant_name' => $this->faker->name,
            'applicant_position' => $this->faker->jobTitle . ' ' . $this->faker->company,
            'applicant_agency_id' =>  $this->faskes->id,
            'applicant_agency_name' => $this->faskes->nama_faskes,
            'type' => 'vaccine',
            'letter_url' => $this->faker->url,
            'instance_list' => []
        ];

        $this
            ->actingAs($this->admin, 'api')->json('POST', '/api/v1/allocation-vaccine-request', $payload)
            ->assertStatus(422);
    }

    public function testStoreAllocationRequestNoAllocationMaterialRequest()
    {
        $payload = [
            'letter_number' => $this->faker->numerify('SURAT/' . date('Y/m/d') . '/' . $this->faker->company . '/####'),
            'letter_date' => date('Y-m-d'),
            'applicant_name' => $this->faker->name,
            'applicant_position' => $this->faker->jobTitle . ' ' . $this->faker->company,
            'applicant_agency_id' =>  $this->faskes->id,
            'applicant_agency_name' => $this->faskes->nama_faskes,
            'type' => 'vaccine',
            'letter_url' => $this->faker->url,
            'instance_list' => [
                [
                    'agency_id' => $this->nonFaskes->id,
                    'agency_name' => $this->nonFaskes->nama_faskes,
                    'distribution_plan_date' => date('Y-m-d'),
                    'allocation_material_requests' => []
                ]
            ]
        ];

        $this
            ->actingAs($this->admin, 'api')->json('POST', '/api/v1/allocation-vaccine-request', $payload)
            ->assertStatus(422);
    }

    public function testStoreAllocationRequestFailAllocationMaterialRequest()
    {
        $payload = [
            'letter_number' => $this->faker->numerify('SURAT/' . date('Y/m/d') . '/' . $this->faker->company . '/####'),
            'letter_date' => date('Y-m-d'),
            'applicant_name' => $this->faker->name,
            'applicant_position' => $this->faker->jobTitle . ' ' . $this->faker->company,
            'applicant_agency_id' =>  $this->faskes->id,
            'applicant_agency_name' => $this->faskes->nama_faskes,
            'type' => 'vaccine',
            'letter_url' => $this->faker->url,
            'instance_list' => [
                [
                    'agency_id' => $this->nonFaskes->id,
                    'agency_name' => $this->nonFaskes->nama_faskes,
                    'distribution_plan_date' => date('Y-m-d'),
                    'allocation_material_requests' => [
                        [
                            'matg_id' => $this->allocationMaterial->matg_id,
                            'material_id' => $this->allocationMaterial->material_id,
                            'qty' => rand(),
                            'UoM' => $this->allocationMaterial->UoM
                        ]
                    ]
                ]
            ]
        ];

        $this
            ->actingAs($this->admin, 'api')->json('POST', '/api/v1/allocation-vaccine-request', $payload)
            ->assertStatus(422);
    }

    public function testStoreAllocationRequest()
    {
        $payload = [
            'letter_number' => $this->faker->numerify('SURAT/' . date('Y/m/d') . '/' . $this->faker->company . '/####'),
            'letter_date' => date('Y-m-d'),
            'applicant_name' => $this->faker->name,
            'applicant_position' => $this->faker->jobTitle . ' ' . $this->faker->company,
            'applicant_agency_id' =>  $this->faskes->id,
            'applicant_agency_name' => $this->faskes->nama_faskes,
            'type' => 'vaccine',
            'letter_url' => $this->faker->url,
            'instance_list' => [
                [
                    'agency_id' => $this->nonFaskes->id,
                    'agency_name' => $this->nonFaskes->nama_faskes,
                    'distribution_plan_date' => date('Y-m-d'),
                    'allocation_material_requests' => [
                        [
                            'matg_id' => $this->allocationMaterial->matg_id,
                            'material_id' => $this->allocationMaterial->material_id,
                            'material_name' => $this->allocationMaterial->material_name,
                            'qty' => rand(),
                            'UoM' => $this->allocationMaterial->UoM
                        ]
                    ]
                ]
            ]
        ];

        $this
            ->actingAs($this->admin, 'api')->json('POST', '/api/v1/allocation-vaccine-request', $payload)
            ->assertSuccessful();
    }
}
