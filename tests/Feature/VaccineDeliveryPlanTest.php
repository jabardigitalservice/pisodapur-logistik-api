<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Districtcities;
use App\Enums\VaccineRequestStatusEnum;
use App\Models\MedicalFacility;
use App\Models\Vaccine\VaccineProduct;
use App\Subdistrict;
use App\Models\Vaccine\VaccineRequest;
use App\Village;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class VaccineDeliveryPlanTest extends TestCase
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

        $this->medicalFacility = factory(MedicalFacility::class)->create();

        Artisan::call('db:seed --class=VaccineProductSeeder');

        $this->vaccineProduct = VaccineProduct::first();
        $unit = $this->vaccineProduct->unit[0]->id;
        $this->logisticItems[] = [
            'product_id' => $this->vaccineProduct->id,
            'category' => $this->vaccineProduct->category,
            'quantity' => rand(),
            'unit' => $unit,
            'description' => $this->faker->text,
            'usage' => $this->faker->text,
            'note' => $this->faker->text,
        ];

        Storage::fake('photos');
        Notification::fake();

        $this->vaccineRequest = factory(VaccineRequest::class)->create([
            'agency_id' => $this->medicalFacility->id,
            'agency_name' => $this->medicalFacility->name,
            'agency_type_id' => $this->medicalFacility->medical_facility_type_id,
            'agency_village_id' => $this->village->kemendagri_desa_kode,
            'agency_district_id' => $this->village->kemendagri_kecamatan_kode,
            'agency_city_id' => $this->village->kemendagri_kabupaten_kode,
            'is_integrated' => 0,
            'status' => VaccineRequestStatusEnum::finalized(),
        ]);
    }

    public function testGetVaccineDeliveryPlanNoAuth()
    {
        $response = $this->json('GET', '/api/v1/delivery-plan', [
            'status' => VaccineRequestStatusEnum::finalized(),
            'is_integrated' => 0,
        ]);
        $response->assertUnauthorized();
    }

    public function testGetVaccineDeliveryPlan()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/delivery-plan', [
            'status' => VaccineRequestStatusEnum::finalized(),
            'is_integrated' => 0,
        ]);
        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    [
                        'delivery_plan_date',
                        'created_at',
                        'vaccine_sprint_id',
                        'vaccine_sprint_letter_number',
                        'id',
                        'letter_number',
                        'agency_name',
                    ]
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next'
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total'
                ]
            ]);
    }
}
