<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\StockTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockTransactionRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly StockTransaction $transaction) {}
}
