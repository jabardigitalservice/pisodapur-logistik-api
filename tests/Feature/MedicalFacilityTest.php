<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class MedicalFacilityTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function testGetAll()
    {
        $response = $this->get('/api/v1/medical-facility');
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testFilterByName()
    {
        $response = $this->json('GET', '/api/v1/medical-facility', ['name' => 'bandung']);
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testFilterByMedicalFacilityTypeId()
    {
        $response = $this->json('GET', '/api/v1/medical-facility', ['medical_facility_type_id' => 1]);
        $response->assertStatus(Response::HTTP_OK);
    }
}
