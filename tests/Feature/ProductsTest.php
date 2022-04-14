<?php

namespace Tests\Feature;

use App\Enums\ProductCategoryEnum;
use App\Product;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

class ProductsTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = factory(User::class)->create();
        $this->product = factory(Product::class)->create();
    }

    public function testGetProducts()
    {
        $response = $this->json('GET', '/api/v1/landing-page-registration/products', [
            'limit' => 10,
            'name' => $this->product->name,
            'user_filter' => 4,
            'category' => ProductCategoryEnum::alkes()
        ]);
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testGetProductById()
    {
        $response = $this->actingAs($this->admin, 'api')->get('/api/v1/products/' . $this->product->id);
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testGetProductUnitById()
    {
        $response = $this->get('/api/v1/landing-page-registration/product-unit/' . $this->product->id);
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testGetProductsTotalRequest()
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

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', '/api/v1/products-total-request');
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testGetProductsTotalRequestPaginate()
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

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', '/api/v1/products-total-request', ['limit' => 10]);
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testGetProductsTopRequest()
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

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->get('/api/v1/products-top-request');
        $response->assertStatus(Response::HTTP_OK);
    }
}
