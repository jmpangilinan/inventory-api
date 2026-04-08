<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Events\LowStockDetected;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    private function actingAsUser(): static
    {
        return $this->withToken($this->user->createToken('test')->plainTextToken);
    }

    #[Test]
    public function can_list_products(): void
    {
        Product::factory()->count(3)->create(['category_id' => $this->category->id]);

        $response = $this->actingAsUser()->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'sku', 'name', 'price', 'stock_quantity', 'low_stock_threshold', 'is_low_stock', 'is_active']],
            ]);
    }

    #[Test]
    public function list_requires_authentication(): void
    {
        $this->getJson('/api/v1/products')->assertUnauthorized();
    }

    #[Test]
    public function can_create_a_product(): void
    {
        Event::fake();

        $response = $this->actingAsUser()->postJson('/api/v1/products', [
            'category_id' => $this->category->id,
            'sku' => 'SKU-001',
            'name' => 'Test Product',
            'price' => 99.99,
            'stock_quantity' => 50,
            'low_stock_threshold' => 10,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.sku', 'SKU-001')
            ->assertJsonPath('data.is_low_stock', false);

        $this->assertDatabaseHas('products', ['sku' => 'SKU-001']);
        Event::assertNotDispatched(LowStockDetected::class);
    }

    #[Test]
    public function creating_product_with_low_stock_fires_event(): void
    {
        Event::fake();

        $this->actingAsUser()->postJson('/api/v1/products', [
            'category_id' => $this->category->id,
            'sku' => 'SKU-LOW',
            'name' => 'Low Stock Product',
            'price' => 9.99,
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
        ]);

        Event::assertDispatched(LowStockDetected::class, function (LowStockDetected $event): bool {
            return $event->product->sku === 'SKU-LOW';
        });
    }

    #[Test]
    public function create_requires_valid_fields(): void
    {
        $response = $this->actingAsUser()->postJson('/api/v1/products', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id', 'sku', 'name', 'price', 'stock_quantity']);
    }

    #[Test]
    public function create_requires_existing_category(): void
    {
        $response = $this->actingAsUser()->postJson('/api/v1/products', [
            'category_id' => 999,
            'sku' => 'SKU-001',
            'name' => 'Test',
            'price' => 10,
            'stock_quantity' => 10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);
    }

    #[Test]
    public function can_show_a_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAsUser()->getJson("/api/v1/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.sku', $product->sku);
    }

    #[Test]
    public function show_returns_404_for_missing_product(): void
    {
        $this->actingAsUser()->getJson('/api/v1/products/999')->assertNotFound();
    }

    #[Test]
    public function can_update_a_product(): void
    {
        Event::fake();

        $product = Product::factory()->create(['category_id' => $this->category->id, 'stock_quantity' => 50]);

        $response = $this->actingAsUser()->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Product',
            'stock_quantity' => 50,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Product');

        Event::assertNotDispatched(LowStockDetected::class);
    }

    #[Test]
    public function updating_product_to_low_stock_fires_event(): void
    {
        Event::fake();

        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 50,
            'low_stock_threshold' => 10,
        ]);

        $this->actingAsUser()->putJson("/api/v1/products/{$product->id}", [
            'stock_quantity' => 3,
        ]);

        Event::assertDispatched(LowStockDetected::class);
    }

    #[Test]
    public function can_delete_a_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAsUser()->deleteJson("/api/v1/products/{$product->id}");

        $response->assertOk()->assertJson(['message' => 'Product deleted successfully.']);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    #[Test]
    public function can_get_low_stock_products(): void
    {
        Product::factory()->count(2)->lowStock()->create(['category_id' => $this->category->id]);
        Product::factory()->count(3)->create(['category_id' => $this->category->id, 'stock_quantity' => 50]);

        $response = $this->actingAsUser()->getJson('/api/v1/products/low-stock');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
