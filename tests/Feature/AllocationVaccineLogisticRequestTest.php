<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\MasterFaskes;
use App\AllocationMaterial;
use App\AllocationDistributionRequest;
use App\AllocationMaterialRequest;
use App\AllocationRequest;
use App\Enums\AllocationRequestStatusEnum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response = $this->get('/api/v1/allocation-vaccine-request');
        $response->assertUnauthorized();
    }

    public function testGetAllocationVaccineLogisticRequestByAllocationRequestNoAuth()
    {
        $allocationRequest = $this->allocationRequest->id;
        $response = $this->get('/api/v1/allocation-vaccine-request/' . $allocationRequest);
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
        $response = $this->actingAs($this->admin, 'api')->get('/api/v1/allocation-vaccine-request/' . $allocationRequest);
        $response->assertSuccessful();
    }
}
