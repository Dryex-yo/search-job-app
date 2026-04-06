<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MultitenancyBootstrapProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Multitenancy is automatically registered by Spatie\Multitenancy package
        // Use Tenancy facade from Spatie\Multitenancy\Facades\Tenancy
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
