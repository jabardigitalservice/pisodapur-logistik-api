<?php

namespace Tests\Feature;

use App\AuthKey;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use Illuminate\Http\Response;

class AuthKeyTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = factory(User::class)->create();
        $this->authKey = factory(AuthKey::class)->create();
    }

    public function testRegister()
    {
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/auth-key/register', [
            'name' => $this->faker->name
        ]);
        $response->assertSuccessful();
    }

    public function testResetNotFound()
    {
        $generateToken = bin2hex(openssl_random_pseudo_bytes(16));
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/auth-key/reset', [
            'name' => $this->faker->name,
            'token' => $generateToken,
            'retoken' => $generateToken
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testResetSuccess()
    {
        $generateToken = bin2hex(openssl_random_pseudo_bytes(16));
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/auth-key/reset', [
            'name' => $this->authKey->name,
            'token' => $this->authKey->token,
            'retoken' => $this->authKey->token
        ]);
        $response->assertSuccessful();
    }
}
