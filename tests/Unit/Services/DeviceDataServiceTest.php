<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\StockReason;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\DeviceDataService;
use App\Services\StockTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeviceDataServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_processes_device_payload_and_records_transaction(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 20]);

        $expectedTransaction = StockTransaction::factory()->create([
            'product_id' => $product->id,
            'user_id' => User::factory()->create()->id,
        ]);

        $stockService = Mockery::mock(StockTransactionService::class);
        $stockService->shouldReceive('record')
            ->once()
            ->withArgs(function (array $data, User $actor): bool {
                return $data['reason'] === StockReason::DeviceScanned->value
                    && $actor->email === 'device@system.local';
            })
            ->andReturn($expectedTransaction);

        $service = new DeviceDataService($stockService);

        $result = $service->process([
            'device_id' => 'SCANNER-001',
            'device_type' => 'barcode_scanner',
            'product_id' => $product->id,
            'quantity' => 5,
            'type' => TransactionType::Out->value,
        ]);

        $this->assertInstanceOf(StockTransaction::class, $result);
    }

    #[Test]
    public function it_creates_system_user_if_not_exists(): void
    {
        $this->assertDatabaseMissing('users', ['email' => 'device@system.local']);

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 20]);

        $service = app(DeviceDataService::class);
        $service->process([
            'device_id' => 'SCANNER-001',
            'device_type' => 'barcode_scanner',
            'product_id' => $product->id,
            'quantity' => 5,
            'type' => TransactionType::Out->value,
        ]);

        $this->assertDatabaseHas('users', ['email' => 'device@system.local']);
    }

    #[Test]
    public function it_reuses_existing_system_user(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 30]);

        $service = app(DeviceDataService::class);

        $service->process([
            'device_id' => 'SCANNER-001',
            'device_type' => 'barcode_scanner',
            'product_id' => $product->id,
            'quantity' => 5,
            'type' => TransactionType::Out->value,
        ]);

        $service->process([
            'device_id' => 'SCANNER-001',
            'device_type' => 'barcode_scanner',
            'product_id' => $product->id,
            'quantity' => 5,
            'type' => TransactionType::Out->value,
        ]);

        $this->assertSame(1, User::where('email', 'device@system.local')->count());
    }
}
