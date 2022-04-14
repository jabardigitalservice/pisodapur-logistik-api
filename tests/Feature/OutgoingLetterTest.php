<?php

namespace Tests\Feature;

use App\Agency;
use App\Applicant;
use App\MasterFaskes;
use App\OutgoingLetter;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class OutgoingLetterTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = factory(User::class)->create();
        $this->faskes = factory(MasterFaskes::class)->create();
        $this->agency = factory(Agency::class)->create([
            'master_faskes_id' => $this->faskes->id,
            'agency_type' => $this->faskes->id_tipe_faskes,
        ]);
        $this->applicant = factory(Applicant::class)->create(['agency_id' => $this->agency->id]);
        $this->outgoingLetter = factory(OutgoingLetter::class)->create(['user_id' => $this->admin->id]);
    }

    public function testStoreOutgoingLetterFail()
    {
        $letterRequest[] = [
            'applicant_id' => $this->agency->id
        ];
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/outgoing-letter', [
            'letter_name' => $this->faker->name,
            'letter_date' => date('Y-m-d H:i:s'),
            'letter_request' => $letterRequest,
        ]);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testStoreOutgoingLetter()
    {
        $letterRequest[] = [
            'applicant_id' => $this->agency->id
        ];
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/outgoing-letter', [
            'letter_name' => $this->faker->name,
            'letter_date' => date('Y-m-d H:i:s'),
            'letter_request' => json_encode($letterRequest),
        ]);
        $response->assertSuccessful();
    }

    public function testUploadOutgoingLetter()
    {
        Storage::fake('photos');
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/outgoing-letter/upload', [
            'id' => $this->outgoingLetter->id,
            'letter_number' => $this->outgoingLetter->letter_number,
            'file' => UploadedFile::fake()->image('letter_file.jpg'),
        ]);
        $response->assertSuccessful();
    }

    public function testGetOutgoingLetter()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/outgoing-letter', [
            'sort' => 'desc',
            'limit' => 10,
            'letter_date' => date('Y-m-d H:i:s'),
            'letter_number' => $this->faker->name
        ]);
        $response->assertSuccessful();
    }

    public function testGetOutgoingLetterById()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/outgoing-letter/' . $this->outgoingLetter->id);
        $response->assertSuccessful();
    }

    public function testPrintOutgoingLetterById()
    {
        $letterRequest[] = [
            'applicant_id' => $this->agency->id
        ];
        $data = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/outgoing-letter', [
            'letter_name' => $this->faker->name,
            'letter_date' => date('Y-m-d H:i:s'),
            'letter_request' => json_encode($letterRequest),
        ]);

        $data = $data->decodeResponseJson();
        $id = $data['data']['outgoing_letter']['id'];
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/outgoing-letter-print/' . $id);
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                  'image' => [
                    'pemprov',
                    'divlog',
                  ],
                  'outgoing_letter' => [
                    'id',
                    'letter_number',
                    'letter_date',
                  ],
                  'request_letter' => [],
                  'material' => []
                ]
              ]);
    }
}
