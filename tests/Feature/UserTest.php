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

    public function testPostLoginMedicalSuccess()
    {
        $user = factory(User::class)->create([
            'username'    => 'username@example.net',
            'password' => bcrypt('secret'),
            'app' => 'medical'
        ]);

        $response = $this->post('/api/v1/login', [
            'username'    => 'username@example.net',
            'password' => 'secret',
        ]);
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                "status",
                "message",
                "data" => [
                  "token",
                  "user" => [
                    "id",
                    "name",
                    "username",
                    "name_district_city",
                    "code_district_city",
                    "roles",
                    "agency_name",
                    "email",
                    "email_verified_at",
                    "created_at",
                    "updated_at",
                    "handphone",
                    "phase",
                    "app",
                  ]
                ]
            ]);
    }

    public function testPostLoginVaccineSuccess()
    {
        $user = factory(User::class)->create([
            'username'    => 'usernamevaccine@example.net',
            'password' => bcrypt('secret'),
            'app' => 'vaccine'
        ]);

        $response = $this->post('/api/v1/login', [
            'username'    => 'usernamevaccine@example.net',
            'password' => 'secret',
        ]);
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                "status",
                "message",
                "data" => [
                  "token",
                  "user" => [
                    "id",
                    "name",
                    "username",
                    "name_district_city",
                    "code_district_city",
                    "roles",
                    "agency_name",
                    "email",
                    "email_verified_at",
                    "created_at",
                    "updated_at",
                    "handphone",
                    "phase",
                    "app",
                  ]
                ]
            ]);
    }

    public function testRegisterUserMedical()
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
            'app' => 'medical',
        ]);
        $response->assertSuccessful();
    }

    public function testRegisterUserVaccine()
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
            'app' => 'vaccine',
        ]);
        $response->assertSuccessful();
    }

    public function testRegisterUserAppOther()
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
            'app' => $this->faker->name,
        ]);
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                "message",
                "errors" => [
                  "app" => []
                ]
              ]
            );
    }
}
