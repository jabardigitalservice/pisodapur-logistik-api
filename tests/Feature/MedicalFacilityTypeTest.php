<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class MedicalFacilityTypeTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function testGetAll()
    {
        $response = $this->get('/api/v1/medical-facility-type');
        $response->assertStatus(Response::HTTP_OK);
    }
}
