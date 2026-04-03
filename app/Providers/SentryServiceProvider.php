<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SentryService;

class SentryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the Sentry service
        $this->app->singleton('sentry', function () {
            return new SentryService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Initialize Sentry if enabled
        if (config('sentry.enabled')) {
            SentryService::initialize();
        }
    }
}
