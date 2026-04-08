<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings will be added here as repositories are created.
        // Example:
        // $this->app->bind(
        //     \App\Repositories\Interfaces\ProductRepositoryInterface::class,
        //     \App\Repositories\Eloquent\ProductRepository::class,
        // );
    }
}
