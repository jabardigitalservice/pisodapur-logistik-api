<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class ChangeStatusNotifyTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = factory(User::class)->create();
    }

    public function testSendNotification()
    {
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/notify', [
            'id' => rand(),
            'url' => $this->faker->url,
            'phase' => $this->faker->name,
        ]);
        $response->assertSuccessful();
    }
}
