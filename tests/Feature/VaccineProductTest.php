<?php

namespace Tests\Feature;

use App\Models\Vaccine\VaccineProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class VaccineProductTest extends TestCase
{
    use WithFaker;
    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed --class=VaccineProductSeeder');
        $this->jsonStructure = [
            'status',
            'message',
            'data' => [
                [
                    'id',
                    'name',
                    'category',
                    'unit' => [
                        [
                            'id',
                            'name'
                        ]
                    ],
                    'api',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ]
            ]
        ];
    }

    public function testGetAll()
    {
        $response = $this->json('GET', '/api/v1/vaccine-product');
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure($this->jsonStructure);
    }

    public function testGetCategoryVaccineOnly()
    {
        $response = $this->json('GET', '/api/v1/vaccine-product', ['category' => 'vaccine']);
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure($this->jsonStructure);
    }

    public function testGetCategoryVaccineSupportOnly()
    {
        $response = $this->json('GET', '/api/v1/vaccine-product', ['category' => 'vaccine_support']);
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure($this->jsonStructure);
    }

    public function testGetCategoryErrorRequest()
    {
        $response = $this->json('GET', '/api/v1/vaccine-product', ['category' => $this->faker->name]);
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'category' => []
                ]
            ]);
    }
}
