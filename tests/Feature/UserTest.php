<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use App\User;

class UserTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function testPostLoginSuccess()
    {
        $user = factory(User::class)->create([
            'username'    => 'username@example.net',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->post('/api/v1/login', [
            'username'    => 'username@example.net',
            'password' => 'secret',
        ]);
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testRegisterUser()
    {
        $admin = factory(User::class)->create();

        $response = $this->actingAs($admin, 'api')->json('POST', '/api/v1/users/register', [
            'name' => $this->faker->name,
            'username' => $this->faker->userName,
            'email' => $this->faker->email,
            'password' => $this->faker->password,
            'roles' => 'dinkesprov',
            'agency_name' => $this->faker->company,
            'code_district_city' => '32.73',
            'name_district_city' => $this->faker->city,
            'phase' => 'surat',
        ]);
        $response->assertSuccessful();
    }
}
