<?php

namespace Tests\Feature;

use App\Agency;
use Tests\TestCase;
use App\User;
use App\Applicant;
use App\Enums\LogisticRatingEnum;
use App\MasterFaskes;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

class LogisticRatingTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = factory(User::class)->create();
        $this->faskes = factory(MasterFaskes::class)->create();
        $this->nonFaskes = factory(MasterFaskes::class)->create(['id_tipe_faskes' => rand(4, 5)]);
        $this->agency = factory(Agency::class)->create([
            'master_faskes_id' => $this->faskes->id,
            'agency_type' => $this->faskes->id_tipe_faskes,
        ]);
        $this->applicant = factory(Applicant::class)->create(['agency_id' => $this->agency->id]);
    }

    public function testPostNoAuth()
    {
        $response = $this->json('POST', '/api/v1/rating', [
            'agency_id' => $this->agency->id,
            'phase' => LogisticRatingEnum::request(),
            'score' => rand(1, 5),
        ]);
        $response->assertSuccessful();
    }

    public function testPostFailed()
    {
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/rating');
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertExactJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'agency_id' => [
                        'The agency id field is required.'
                    ],
                    'phase' => [
                        'The phase field is required.'
                    ],
                    'score' => [
                        'The score field is required.'
                    ],
                ],
            ]);
    }

    public function testPostSuccess()
    {
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/rating', [
            'agency_id' => $this->agency->id,
            'phase' => $this->faker->randomElement(LogisticRatingEnum::getValues()),
            'score' => rand(1, 5),
        ]);
        $response->assertSuccessful();
    }
}
