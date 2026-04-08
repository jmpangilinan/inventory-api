<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\LowStockDetected;
use App\Events\StockTransactionRecorded;
use App\Listeners\AuditStockListener;
use App\Listeners\NotifyLowStockListener;
use Illuminate\Database\Eloquent\Model;
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
        // Strict mode: throw on lazy loading, missing attributes, and silently
        // discarded fillable writes — catches N+1s and typos at development time.
        Model::shouldBeStrict(! $this->app->isProduction());

        Event::listen(LowStockDetected::class, NotifyLowStockListener::class);
        Event::listen(StockTransactionRecorded::class, AuditStockListener::class);
    }
}
