<?php

namespace Tests\Feature;

use App\Models\Vaccine\VaccineRequest;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class VaccineTrackingTest extends TestCase
{
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = factory(User::class)->create();
    }

    public function testGetVaccineTracking()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-tracking', [
            'search' => $this->faker->email
        ]);
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testGetVaccineTrackingDetail()
    {
        $vaccineRequest = VaccineRequest::first();
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-tracking/' . $vaccineRequest->id);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function testGetVaccineProductTracking()
    {
        $vaccineRequest = VaccineRequest::first();
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-product-tracking', [
            'vaccine_request_id' => $vaccineRequest->id
        ]);

        $response->assertStatus(Response::HTTP_OK);
    }
}
