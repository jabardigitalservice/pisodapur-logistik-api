<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

class LeaderTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = factory(User::class)->create();
        Artisan::call('db:seed --class=LeaderSeeder');
    }

    public function testGetLeaderNoAuth()
    {
        $response = $this->json('GET', '/api/v1/leader');
        $response->assertUnauthorized();
    }

    public function testGetLeaderFail()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/leader');
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testGetLeader()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/leader', [
            'phase' => 'finalized'
        ]);
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'fullname',
                    'role',
                ]
            ]);
    }
}
