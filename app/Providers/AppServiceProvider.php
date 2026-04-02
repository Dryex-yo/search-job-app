<?php

namespace App\Providers;

use App\Services\DatabaseSecurityService;
use App\Rules\NoSqlInjection;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Enable database strict mode for enhanced security
        DatabaseSecurityService::enableStrictMode();

        // Register custom validation rules
        Validator::extend('no_sql_injection', function ($attribute, $value, $parameters, $validator) {
            $rule = new NoSqlInjection();
            $failMessages = [];
            
            $rule->validate($attribute, $value, function ($message) use (&$failMessages) {
                $failMessages[] = $message;
            });
            
            return empty($failMessages);
        });
    }
}
