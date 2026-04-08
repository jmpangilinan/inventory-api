<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\LowStockDetected;
use App\Events\StockTransactionRecorded;
use App\Listeners\AuditStockListener;
use App\Listeners\NotifyLowStockListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Intentionally empty — use RepositoryServiceProvider for bindings
    }

    public function boot(): void
    {
        Event::listen(LowStockDetected::class, NotifyLowStockListener::class);
        Event::listen(StockTransactionRecorded::class, AuditStockListener::class);
    }
}
