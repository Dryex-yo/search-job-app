<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

class RateLimitRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Define rate limiting rules based on route/action
        $this->defineRateLimits();

        // Check rate limit for the request
        $key = $this->getThrottleKey($request);
        
        if (RateLimiter::tooManyAttempts($key, $this->limit($request))) {
            return $this->buildResponse($key);
        }

        RateLimiter::hit($key, $this->decay($request));

        return $next($request);
    }

    /**
     * Define rate limiting rules
     */
    protected function defineRateLimits(): void
    {
        // Login attempts: 5 per minute
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // API endpoints: 60 per minute per IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Job applications: 10 per hour per user
        RateLimiter::for('apply', function (Request $request) {
            return Limit::perHour(10)->by($request->user()?->id ?: $request->ip());
        });

        // Search operations: 30 per minute
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Export operations: 5 per hour per user
        RateLimiter::for('export', function (Request $request) {
            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip());
        });

        // Admin operations: 100 per hour per user
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perHour(100)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Get the throttle key for the request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function getThrottleKey(Request $request): string
    {
        // Different keys based on route
        if ($request->routeIs('login')) {
            return "login:{$request->ip()}";
        }

        if ($request->routeIs('jobs.apply')) {
            $userId = $request->user()?->id ?? $request->ip();
            return "apply:{$userId}";
        }

        if ($request->routeIs('admin.applicants.export.excel', 'recruiter.applicants.export')) {
            $userId = $request->user()?->id ?? 'unknown';
            return "export:{$userId}";
        }

        return "default:{$request->ip()}";
    }

    /**
     * Get the limit for the request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int
     */
    protected function limit(Request $request): int
    {
        if ($request->routeIs('login')) {
            return 5;
        }

        if ($request->routeIs('jobs.apply')) {
            return 10;
        }

        return 60;
    }

    /**
     * Get the decay (seconds) for the rate limit
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int
     */
    protected function decay(Request $request): int
    {
        if ($request->routeIs('login')) {
            return 60; // 1 minute
        }

        if ($request->routeIs('jobs.apply')) {
            return 3600; // 1 hour
        }

        return 60; // 1 minute default
    }

    /**
     * Build the response for rate limit exceeded
     *
     * @param  string  $key
     * @return \Illuminate\Http\Response
     */
    protected function buildResponse(string $key)
    {
        return response()->json([
            'message' => 'Too many requests. Please try again later.',
        ], 429);
    }
}
