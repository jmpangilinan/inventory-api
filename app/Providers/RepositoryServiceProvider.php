<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Intentionally empty — repository interface bindings are added here
        // as each feature repository is implemented. See docs/adr/001-repository-pattern.md
    }
}
