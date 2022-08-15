<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\AllocationMaterial;
use App\Districtcities;
use App\Enums\Vaccine\VaccineRequestRatingEnum;
use App\Models\MedicalFacility;
use App\Subdistrict;
use App\Models\Vaccine\VaccineRequest;
use App\Village;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

class VaccineRequestRatingTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = factory(User::class)->create();
        $this->districtcities = factory(Districtcities::class)->create();
        $this->subdistricts = factory(Subdistrict::class)->create([
            'kemendagri_kabupaten_kode' => $this->districtcities->kemendagri_kabupaten_kode,
            'kemendagri_kabupaten_nama' => $this->districtcities->kemendagri_kabupaten_nama,
        ]);
        $this->village = factory(Village::class)->create([
            'kemendagri_provinsi_kode' => '32',
            'kemendagri_provinsi_nama' => 'JAWA BARAT',
            'kemendagri_kabupaten_kode' => $this->subdistricts->kemendagri_kabupaten_kode,
            'kemendagri_kabupaten_nama' => $this->subdistricts->kemendagri_kabupaten_nama,
            'kemendagri_kecamatan_kode' => $this->subdistricts->kemendagri_kecamatan_kode,
            'kemendagri_kecamatan_nama' => $this->subdistricts->kemendagri_kecamatan_nama,
        ]);
        $this->allocationMaterial = factory(AllocationMaterial::class)->create();

        $this->medicalFacility = factory(MedicalFacility::class)->create();
        $this->vaccineRequest = factory(VaccineRequest::class)->create([
            'agency_id' => $this->medicalFacility->id,
            'agency_name' => $this->medicalFacility->name,
            'agency_type_id' => $this->medicalFacility->medical_facility_type_id,
            'agency_village_id' => $this->village->kemendagri_desa_kode,
            'agency_district_id' => $this->village->kemendagri_kecamatan_kode,
            'agency_city_id' => $this->village->kemendagri_kabupaten_kode,
        ]);
    }

    public function testPostNoAuth()
    {
        $response = $this->json('POST', '/api/v1/vaccine-rating', [
            'vaccine_request_id' => $this->vaccineRequest->id,
            'phase' => VaccineRequestRatingEnum::request(),
            'score' => rand(1, 5),
        ]);
        $response->assertSuccessful();
    }

    public function testPostFailed()
    {
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-rating');
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertExactJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'vaccine_request_id' => [
                        'The vaccine request id field is required.'
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
        $response = $this->actingAs($this->admin, 'api')->json('POST', '/api/v1/vaccine-rating', [
            'vaccine_request_id' => $this->vaccineRequest->id,
            'phase' => $this->faker->randomElement(VaccineRequestRatingEnum::getValues()),
            'score' => rand(1, 5),
        ]);
        $response->assertSuccessful();
    }
}
