<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Custom EncryptCookies middleware (extends Laravel's EncryptCookies with $except = ['app_locale'])
        // is automatically detected and used instead of the default one

        // SetLocale MUST run FIRST before EncryptCookies
        // so it can read the app_locale cookie
        $middleware->web(prepend: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\EnsureTenantContextMiddleware::class,
            \App\Http\Middleware\PerformanceHeaders::class,
            \App\Http\Middleware\AllowBulkOperations::class,  // Must run BEFORE PreventSqlInjection
            \App\Http\Middleware\PreventSqlInjection::class,
            \App\Http\Middleware\RateLimitMiddleware::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Register route middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'recruiter' => \App\Http\Middleware\RecruiterMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sentry integration for error tracking
        $exceptions->reportable(function (\Throwable $e) {
            // Send to Sentry if enabled
            if (config('sentry.enabled') && function_exists('\\Sentry\\captureException')) {
                \Sentry\captureException($e);
            }
        });

        // Custom error response handler
        $exceptions->render(function (\Throwable $e, $request) {
            // Log to Sentry with context
            if (config('sentry.enabled') && function_exists('\\Sentry\\withScope')) {
                \Sentry\withScope(function ($scope) use ($e, $request) {
                    // Add user context
                    if ($request->user()) {
                        $scope->setUser([
                            'id' => $request->user()->id,
                            'email' => $request->user()->email,
                            'username' => $request->user()->name ?? null,
                        ]);
                    }

                    // Add request context
                    $scope->setContext('request', [
                        'url' => $request->url(),
                        'method' => $request->method(),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);

                    // Add request data
                    if ($request->isJson()) {
                        $scope->setContext('payload', $request->all());
                    }
                });
            }
        });
    })->create();

