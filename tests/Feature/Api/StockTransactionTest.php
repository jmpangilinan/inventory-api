<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\StockReason;
use App\Enums\TransactionType;
use App\Events\LowStockDetected;
use App\Events\StockTransactionRecorded;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->user = User::factory()->create();
        $category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 50,
            'low_stock_threshold' => 10,
        ]);
    }

    private function actingAsUser(): static
    {
        return $this->withToken($this->user->createToken('test')->plainTextToken);
    }

    #[Test]
    public function can_list_transactions_for_a_product(): void
    {
        StockTransaction::factory()->count(3)->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAsUser()
            ->getJson("/api/v1/products/{$this->product->id}/transactions");

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    #[Test]
    public function can_record_stock_in(): void
    {
        Event::fake();

        $response = $this->actingAsUser()->postJson('/api/v1/stock-transactions', [
            'product_id' => $this->product->id,
            'type' => TransactionType::In->value,
            'reason' => StockReason::Purchase->value,
            'quantity' => 20,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'in')
            ->assertJsonPath('data.stock_before', 50)
            ->assertJsonPath('data.stock_after', 70);

        $this->assertDatabaseHas('products', ['id' => $this->product->id, 'stock_quantity' => 70]);
        Event::assertDispatched(StockTransactionRecorded::class);
    }

    #[Test]
    public function can_record_stock_out(): void
    {
        Event::fake();

        $response = $this->actingAsUser()->postJson('/api/v1/stock-transactions', [
            'product_id' => $this->product->id,
            'type' => TransactionType::Out->value,
            'reason' => StockReason::Sale->value,
            'quantity' => 10,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.stock_before', 50)
            ->assertJsonPath('data.stock_after', 40);

        $this->assertDatabaseHas('products', ['id' => $this->product->id, 'stock_quantity' => 40]);
    }

    #[Test]
    public function can_record_stock_adjustment(): void
    {
        Event::fake();

        $response = $this->actingAsUser()->postJson('/api/v1/stock-transactions', [
            'product_id' => $this->product->id,
            'type' => TransactionType::Adjustment->value,
            'reason' => StockReason::Correction->value,
            'quantity' => 35,
            'notes' => 'Physical count correction',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.stock_after', 35);

        $this->assertDatabaseHas('products', ['id' => $this->product->id, 'stock_quantity' => 35]);
    }

    #[Test]
    public function stock_out_fails_when_insufficient_stock(): void
    {
        $response = $this->actingAsUser()->postJson('/api/v1/stock-transactions', [
            'product_id' => $this->product->id,
            'type' => TransactionType::Out->value,
            'reason' => StockReason::Sale->value,
            'quantity' => 100,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Insufficient stock. Available: 50, requested: 100.');

        $this->assertDatabaseHas('products', ['id' => $this->product->id, 'stock_quantity' => 50]);
    }

    #[Test]
    public function stock_out_fires_low_stock_event_when_threshold_reached(): void
    {
        Event::fake();

        $this->actingAsUser()->postJson('/api/v1/stock-transactions', [
            'product_id' => $this->product->id,
            'type' => TransactionType::Out->value,
            'reason' => StockReason::Sale->value,
            'quantity' => 45,
        ]);

        Event::assertDispatched(LowStockDetected::class);
    }

    #[Test]
    public function store_requires_valid_fields(): void
    {
        $response = $this->actingAsUser()->postJson('/api/v1/stock-transactions', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['product_id', 'type', 'reason', 'quantity']);
    }

    #[Test]
    public function store_rejects_invalid_transaction_type(): void
    {
        $response = $this->actingAsUser()->postJson('/api/v1/stock-transactions', [
            'product_id' => $this->product->id,
            'type' => 'invalid-type',
            'reason' => StockReason::Purchase->value,
            'quantity' => 10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    #[Test]
    public function store_requires_authentication(): void
    {
        $this->postJson('/api/v1/stock-transactions', [])->assertUnauthorized();
    }
}
