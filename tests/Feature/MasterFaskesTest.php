<?php

namespace Tests\Feature;

use App\Agency;
use App\Applicant;
use App\MasterFaskes;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MasterFaskesTest extends TestCase
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

    public function testGetMasterFaskes()
    {
        $response = $this->actingAs($this->admin, 'api')->get('/api/v1/master-faskes');
        $response->assertSuccessful();
    }

    public function testGetMasterFaskesById()
    {
        $response = $this->actingAs($this->admin, 'api')->get('/api/v1/master-faskes/' . $this->faskes->id);
        $response->assertSuccessful();
    }

    public function testStoreMasterFaskes()
    {
        Storage::fake('photos');

        $faskesName = 'FASKES ' . $this->faker->state . ' ' . $this->faker->company;
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/master-faskes', [
            'nama_faskes' => $faskesName,
            'id_tipe_faskes' => rand(1, 5),
            'nomor_telepon' => $this->faker->phoneNumber,
            'kode_kab_kemendagri' => '32.01',
            'kode_kec_kemendagri' => '32.01.01',
            'kode_kel_kemendagri' => '32.01.01.1001',
            'alamat' => $this->faker->address
        ]);
        $response->assertSuccessful();
    }

    public function testPostVerifyingMasterFaskes()
    {
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/verify-master-faskes/' . $this->faskes->id, [
            'verification_status' => 'verified'
        ]);
        $response->assertSuccessful();
    }

    public function testPostRejectingMasterFaskes()
    {
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/verify-master-faskes/' . $this->faskes->id, [
            'verification_status' => 'rejected'
        ]);
        $response->assertSuccessful();
    }

    public function testGetMasterFaskesTypeList()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/master-faskes-type', [
            'is_imported' => rand(0, 1),
            'non_public' => rand(0, 1),
        ]);
        $response->assertSuccessful();
    }

    public function testGetFaskesTypeTotalRequest()
    {
        $admin = factory(User::class)->create([
            'username'    => 'username@example.net',
            'password' => bcrypt('secret'),
        ]);

        $login = $this->post('/api/v1/login', [
            'username'    => 'username@example.net',
            'password' => 'secret',
        ]);

        $responseData = $login->json();
        $token = $responseData['data']['token'];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', '/api/v1/faskes-type-total-request');
        $response->assertSuccessful();
    }

    public function testGetFaskesTypeTopRequest()
    {
        $admin = factory(User::class)->create([
            'username'    => 'username@example.net',
            'password' => bcrypt('secret'),
        ]);

        $login = $this->post('/api/v1/login', [
            'username'    => 'username@example.net',
            'password' => 'secret',
        ]);

        $responseData = $login->json();
        $token = $responseData['data']['token'];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', '/api/v1/faskes-type-top-request');
        $response->assertSuccessful();
    }
}
