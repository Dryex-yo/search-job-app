<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SentryService;

class SentryContextMiddleware
{
    /**
     * Handle an incoming request and set Sentry context.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set user context if authenticated
        if ($request->user()) {
            SentryService::setUserContext($request->user());
            
            // Add user role as tag
            if ($request->user()->role) {
                SentryService::setTag('user_role', $request->user()->role);
            }
        }

        // Add request context
        SentryService::addBreadcrumb(
            $request->method() . ' ' . $request->path(),
            [
                'method' => $request->method(),
                'path' => $request->path(),
                'ip' => $request->ip(),
            ],
            'request'
        );

        // Set common tags
        SentryService::setTag('route', $request->route()?->getName() ?? 'unknown');
        SentryService::setTag('environment', config('app.env'));

        return $next($request);
    }
}
