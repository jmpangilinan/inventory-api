<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\StockTransactionRecorded;
use Illuminate\Support\Facades\Log;

class AuditStockListener
{
    public function handle(StockTransactionRecorded $event): void
    {
        $transaction = $event->transaction;

        Log::info('Stock transaction recorded', [
            'transaction_id' => $transaction->id,
            'product_id' => $transaction->product_id,
            'type' => $transaction->type->value,
            'reason' => $transaction->reason->value,
            'quantity' => $transaction->quantity,
            'stock_before' => $transaction->stock_before,
            'stock_after' => $transaction->stock_after,
            'user_id' => $transaction->user_id,
        ]);
    }
}
