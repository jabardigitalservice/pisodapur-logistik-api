<?php

namespace Tests\Feature;

use App\Agency;
use App\Applicant;
use App\Districtcities;
use App\MasterFaskes;
use App\MasterFaskesType;
use App\Subdistrict;
use App\User;
use App\Village;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
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
        $this->faskesType1 = factory(MasterFaskesType::class)->create(['id' => 1]);
        $this->faskesType2 = factory(MasterFaskesType::class)->create(['id' => 2]);
        $this->faskesType3 = factory(MasterFaskesType::class)->create(['id' => 3]);
        $this->faskesType4 = factory(MasterFaskesType::class)->create(['id' => 4]);
        $this->faskesType5 = factory(MasterFaskesType::class)->create(['id' => 5]);
        $this->districtcities = factory(Districtcities::class)->create();
        $this->subdistrict = factory(Subdistrict::class)->create();
        $this->village = factory(Village::class)->create();
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

    public function testGetMasterFaskesFilterByIspaginated()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/master-faskes', [
            'is_paginated' => rand(0, 1),
        ]);
        $response->assertSuccessful();
    }

    public function testGetMasterFaskesFilterByVerificationStatus()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/master-faskes', [
            'verification_status' => 'not_verified',
        ]);
        $response->assertSuccessful();
    }

    public function testGetMasterFaskesFilterByNamaFaskes()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/master-faskes', [
            'nama_faskes' => 'RSUD',
        ]);
        $response->assertSuccessful();
    }

    public function testGetMasterFaskesFilterByIdTipeFaskes()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/master-faskes', [
            'id_tipe_faskes' => rand(1, 5),
        ]);

        $response->assertSuccessful();
    }

    public function testGetMasterFaskesFilterByIsFaskes()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/master-faskes', [
            'is_faskes' => rand(0, 1),
        ]);
        $response->assertSuccessful();
    }

    public function testGetMasterFaskesById()
    {
        $response = $this->actingAs($this->admin, 'api')->get('/api/v1/master-faskes/' . $this->faskes->id);
        $response->assertSuccessful();
    }

    public function testStoreMasterFaskes()
    {
        $faskesName = 'FASKES ' . $this->faker->state . ' ' . $this->faker->company;
        $data = [
            'nama_faskes' => $faskesName,
            'id_tipe_faskes' => 5,
            'nomor_telepon' => '+6281098765432',
            'kode_kab_kemendagri' => '32.01',
            'kode_kec_kemendagri' => '32.01.01',
            'kode_kel_kemendagri' => '32.01.01.1001',
            'alamat' => 'jl. ' . $this->faker->company . ' No. ' . rand()
        ];

        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/master-faskes', $data);
        $response->assertStatus(Response::HTTP_OK);
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
