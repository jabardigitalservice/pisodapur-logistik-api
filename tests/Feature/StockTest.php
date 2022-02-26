<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class StockTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = factory(\App\User::class)->create();
        $this->product = factory(\App\Product::class)->create();
    }

    public function testGetStockNotAuth()
    {
        $response = $this->get('/api/v1/stock');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetStockByProductId()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/stock', ['id' => $this->product]);
        $response->assertSuccessful();
    }

    public function testGetStockByPoslogId()
    {
        $response = $this->actingAs($this->admin, 'api')->json('GET', '/api/v1/stock', ['poslog_id' => 'MAT-1X52BP43']);
        $response->assertSuccessful();
    }
}
