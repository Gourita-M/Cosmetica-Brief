<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
        $this->category = Category::factory()->create();
    }

    public function test_can_list_products()
    {
        Product::factory()->count(3)->create(['category_id' => $this->category->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function test_can_create_product_and_generates_slug()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->postJson('/api/products', [
                             'category_id' => $this->category->id,
                             'name' => 'Test Product',
                             'description' => 'A wonderful test product',
                             'price' => 10.99,
                             'stock' => 50,
                             'image' => 'default.jpg'
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Test Product']);
                 
        $product = Product::where('name', 'Test Product')->first();
        $this->assertNotNull($product->slug, 'Slug should be generated');
        $this->assertEquals('test-product', $product->slug);
    }

    public function test_can_show_product_by_slug()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Specific Product',
        ]);
        
        // Setup slug manually for test isolation if Sluggable isn't hooking in correctly during mass factory creation
        if (!$product->slug) {
            $product->slug = \Illuminate\Support\Str::slug($product->name);
            $product->save();
        }

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->getJson('/api/products/' . $product->slug);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Specific Product']);
    }

    public function test_product_routes_are_protected()
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(401);
    }
}
