<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class VaccineStatusNoteTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = factory(User::class)->create();
        Artisan::call('db:seed --class=VaccineStatusNoteSeeder');
    }

    public function testGetVaccineRequestNoteNoAuth()
    {
        $response = $this->json('GET', '/api/v1/vaccine-status-note');
        $response->assertUnauthorized();
    }

    public function testGetVaccineRequestNote()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/vaccine-status-note');
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                    ]
                ]
            ]);
    }
}
