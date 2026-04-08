<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners;

use App\Events\LowStockDetected;
use App\Listeners\NotifyLowStockListener;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotifyLowStockListenerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_logs_a_warning_when_low_stock_is_detected(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Low stock alert', \Mockery::on(function (array $context): bool {
                return isset($context['product_id'], $context['sku'], $context['stock_quantity']);
            }));

        $category = Category::factory()->create();
        $product = Product::factory()->lowStock()->create(['category_id' => $category->id]);

        $listener = new NotifyLowStockListener;
        $listener->handle(new LowStockDetected($product));
    }
}
