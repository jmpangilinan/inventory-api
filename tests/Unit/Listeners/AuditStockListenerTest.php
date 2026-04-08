<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners;

use App\Events\StockTransactionRecorded;
use App\Listeners\AuditStockListener;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditStockListenerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_logs_stock_transaction_info(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Stock transaction recorded', \Mockery::on(function (array $context): bool {
                return isset(
                    $context['transaction_id'],
                    $context['product_id'],
                    $context['type'],
                    $context['quantity'],
                    $context['stock_before'],
                    $context['stock_after'],
                );
            }));

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $user = User::factory()->create();
        $transaction = StockTransaction::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $listener = new AuditStockListener;
        $listener->handle(new StockTransactionRecorded($transaction));
    }
}
