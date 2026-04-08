<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->user = User::factory()->create();
    }

    private function actingAsUser(): static
    {
        return $this->withToken($this->user->createToken('test')->plainTextToken);
    }

    #[Test]
    public function can_list_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->actingAsUser()->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'name', 'slug', 'description', 'is_active', 'created_at', 'updated_at']],
                'meta',
                'links',
            ]);
    }

    #[Test]
    public function list_requires_authentication(): void
    {
        $this->getJson('/api/v1/categories')->assertUnauthorized();
    }

    #[Test]
    public function can_create_a_category(): void
    {
        $response = $this->actingAsUser()->postJson('/api/v1/categories', [
            'name' => 'Electronics',
            'description' => 'Electronic devices and accessories',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Electronics')
            ->assertJsonPath('data.slug', 'electronics');

        $this->assertDatabaseHas('categories', ['name' => 'Electronics', 'slug' => 'electronics']);
    }

    #[Test]
    public function create_requires_unique_name(): void
    {
        Category::factory()->create(['name' => 'Electronics']);

        $response = $this->actingAsUser()->postJson('/api/v1/categories', [
            'name' => 'Electronics',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function create_requires_name(): void
    {
        $response = $this->actingAsUser()->postJson('/api/v1/categories', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function can_show_a_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAsUser()->getJson("/api/v1/categories/{$category->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.name', $category->name);
    }

    #[Test]
    public function show_returns_404_for_missing_category(): void
    {
        $response = $this->actingAsUser()->getJson('/api/v1/categories/999');

        $response->assertNotFound();
    }

    #[Test]
    public function can_update_a_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAsUser()->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.slug', 'new-name');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
    }

    #[Test]
    public function can_delete_a_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAsUser()->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Category deleted successfully.']);

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    #[Test]
    public function delete_returns_404_for_missing_category(): void
    {
        $response = $this->actingAsUser()->deleteJson('/api/v1/categories/999');

        $response->assertNotFound();
    }
}
