<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Tymon\JWTAuth\Facades\JWTAuth;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_can_list_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function test_can_create_category()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->postJson('/api/categories', [
                             'name' => 'Skincare',
                             'slug' => 'skincare'
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Skincare']);
        $this->assertDatabaseHas('categories', ['name' => 'Skincare']);
    }

    public function test_can_show_category()
    {
        $category = Category::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->getJson('/api/categories/' . $category->id);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => $category->name]);
    }

    public function test_can_update_category()
    {
        $category = Category::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->putJson('/api/categories/' . $category->id, [
                             'name' => 'Updated Name',
                             'slug' => 'updated-name'
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);
        $this->assertDatabaseHas('categories', ['name' => 'Updated Name']);
    }

    public function test_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->deleteJson('/api/categories/' . $category->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_routes_are_protected()
    {
        $response = $this->getJson('/api/categories');
        $response->assertStatus(401);
    }
}
