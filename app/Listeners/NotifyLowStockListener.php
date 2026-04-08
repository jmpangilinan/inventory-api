<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LowStockDetected;
use Illuminate\Support\Facades\Log;

class NotifyLowStockListener
{
    public function handle(LowStockDetected $event): void
    {
        // In production: dispatch notification (email, Slack, push).
        // Using Log for now — replace with Notification::send() when
        // notification channels are configured.
        Log::warning('Low stock alert', [
            'product_id' => $event->product->id,
            'sku' => $event->product->sku,
            'name' => $event->product->name,
            'stock_quantity' => $event->product->stock_quantity,
            'low_stock_threshold' => $event->product->low_stock_threshold,
        ]);
    }
}
