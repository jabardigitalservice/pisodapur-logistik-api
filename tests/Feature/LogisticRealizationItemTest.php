<?php

namespace Tests\Feature;

use App\LogisticRealizationItems;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class LogisticRealizationItemTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = factory(\App\User::class)->create();
        $this->faskes = factory(\App\MasterFaskes::class)->create();
        $this->agency = factory(\App\Agency::class)->create([
            'master_faskes_id' => $this->faskes->id,
            'agency_type' => $this->faskes->id_tipe_faskes,
        ]);
        $this->applicant = factory(\App\Applicant::class)->create(['agency_id' => $this->agency->id]);

        $this->param = [
            'agency_id' => $this->agency->id,
            'product_id' => $this->faker->numerify('MAT-########'),
            'status' => 'approved',
            'usage' => $this->faker->text,
        ];
    }

    public function testGetByAgencyIdNoAuth()
    {
        $this
            ->json('GET', '/api/v1/logistic-admin-realization', ['agency_id' => $this->agency->id])
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testAddNoParam()
    {
        $this
            ->actingAs($this->admin, 'api')
            ->json('POST', '/api/v1/logistic-admin-realization')
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'agency_id' => [],
                    'product_id' => [],
                    'status' => [],
                    'store_type' => [],
                ]
            ]);
    }

    public function testSetRecommendationForNeedId()
    {
        $param = $this->param;
        $param['store_type'] = 'recomendation';
        $param['applicant_id'] = $this->applicant->id;
        $param['need_id'] = rand();
        $param['recommendation_quantity'] = rand(1, 1000);
        $param['recommendation_date'] = date('Y-m-d');
        $param['recommendation_unit'] = 'PCS';

        $this
            ->actingAs($this->admin, 'api')
            ->json('POST', '/api/v1/logistic-request/realization', $param)
            ->assertSuccessful();
    }

    public function testSetRealizationForNeedId()
    {
        $param = $this->param;
        $param['store_type'] = 'realization';
        $param['applicant_id'] = $this->applicant->id;
        $param['need_id'] = rand();
        $param['realization_quantity'] = rand(1, 1000);
        $param['realization_date'] = date('Y-m-d');
        $param['realization_unit'] = null;
        $param['realization_unit_id'] = 'PCS';

        $this
            ->actingAs($this->admin, 'api')
            ->json('POST', '/api/v1/logistic-request/realization', $param)
            ->assertSuccessful();
    }

    public function testAddRecommendationByAdmin()
    {
        $param = $this->param;
        $param['store_type'] = 'recommendation';
        $param['recommendation_quantity'] = rand(1, 1000);
        $param['recommendation_date'] = date('Y-m-d');
        $param['recommendation_unit'] = 'PCS';

        $response = $this
            ->actingAs($this->admin, 'api')
            ->json('POST', '/api/v1/logistic-admin-realization', $param)
            ->assertSuccessful();
    }

    public function testAddRealizationByAdmin()
    {
        $param = $this->param;
        $param['store_type'] = 'realization';
        $param['realization_quantity'] = rand(1, 1000);
        $param['realization_date'] = date('Y-m-d');
        $param['realization_unit'] = 'PCS';

        $response = $this
            ->actingAs($this->admin, 'api')
            ->json('POST', '/api/v1/logistic-admin-realization', $param)
            ->assertSuccessful();
    }

    public function testGetByAgencyId()
    {
        $param = $this->param;
        $param['store_type'] = 'recommendation';
        $param['recommendation_quantity'] = rand(1, 1000);
        $param['recommendation_date'] = date('Y-m-d');
        $param['recommendation_unit'] = 'PCS';

        $this
            ->actingAs($this->admin, 'api')
            ->json('POST', '/api/v1/logistic-admin-realization', $param);

        $this
            ->actingAs($this->admin, 'api')->json('GET', '/api/v1/logistic-admin-realization', ['agency_id' => $this->agency->id])
            ->assertSuccessful()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    [
                        'current_page',
                        'data' => [
                            [
                                "product_id",
                                "product_name",
                                "unit",
                                "date",
                                "quantity",
                                "status",
                                "id",
                                "need_id",
                                "need_product_id",
                                "brand",
                                "category",
                                "request_quantity",
                            ]
                        ],
                        'first_page_url',
                        'from',
                        'last_page',
                        'last_page_url',
                        'next_page_url',
                        'path',
                        'per_page',
                        'prev_page_url',
                        'to',
                        'total',
                    ]
                ]
            ]);
    }

    public function testDelete()
    {
        $param = $this->param;
        $param['store_type'] = 'recommendation';
        $param['recommendation_quantity'] = rand(1, 1000);
        $param['recommendation_date'] = date('Y-m-d');
        $param['recommendation_unit'] = 'PCS';

        $this
            ->actingAs($this->admin, 'api')
            ->json('POST', '/api/v1/logistic-admin-realization', $param);

        $realizationItem = LogisticRealizationItems::first();

        $this
            ->actingAs($this->admin, 'api')->json('DELETE', '/api/v1/logistic-admin-realization/' . $realizationItem->id)
            ->assertSuccessful()
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);
    }

    public function testEditRecommendation()
    {
        $param = $this->param;
        $param['store_type'] = 'recommendation';
        $param['recommendation_quantity'] = rand(1, 1000);
        $param['recommendation_date'] = date('Y-m-d');
        $param['recommendation_unit'] = 'PCS';

        $this
            ->actingAs($this->admin, 'api')
            ->json('POST', '/api/v1/logistic-admin-realization', $param);

        $realizationItem = LogisticRealizationItems::first();

        $param = $this->param;
        $param['store_type'] = 'recommendation';
        $param['recommendation_quantity'] = rand(1, 1000);
        $param['recommendation_date'] = date('Y-m-d');
        $param['recommendation_unit'] = 'PCS';
        $param['realization_quantity'] = null;
        $param['realization_date'] = null;
        $param['realization_unit'] = null;

        $update = $this
            ->actingAs($this->admin, 'api')->json('PUT', '/api/v1/logistic-admin-realization/' . $realizationItem->id, $param)
            ->assertSuccessful();
    }

    public function testEditRealization()
    {
        $param = $this->param;
        $param['store_type'] = 'recommendation';
        $param['recommendation_quantity'] = rand(1, 1000);
        $param['recommendation_date'] = date('Y-m-d');
        $param['recommendation_unit'] = 'PCS';

        $this
            ->actingAs($this->admin, 'api')
            ->json('POST', '/api/v1/logistic-admin-realization', $param);

        $realizationItem = LogisticRealizationItems::first();

        $param = $this->param;
        $param['store_type'] = 'realization';
        $param['realization_quantity'] = rand(1, 1000);
        $param['realization_date'] = date('Y-m-d');
        $param['realization_unit'] = 'PCS';
        $update = $this
            ->actingAs($this->admin, 'api')->json('PUT', '/api/v1/logistic-admin-realization/' . $realizationItem->id, $param)
            ->assertSuccessful();
    }
}
