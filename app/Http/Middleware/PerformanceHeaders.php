<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to add important HTTP headers for security and performance
 * Ensures compliance with SEO and Lighthouse requirements
 */
class PerformanceHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security Headers
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Performance Headers
        $response->header('Accept-CH', 'DPR, Viewport-Width, Width');
        
        // Content Security Policy - Allow Vite dev server in development
        $csp = $this->getContentSecurityPolicy();
        $response->header('Content-Security-Policy', $csp);

        // Cache Control for static assets
        if ($this->isStaticAsset($request)) {
            // Cache for 1 year for versioned assets
            $response->header('Cache-Control', 'public, max-age=31536000, immutable');
        } else {
            // Don't cache dynamic content
            $response->header('Cache-Control', 'public, max-age=0, must-revalidate');
        }

        // Enable compression hint
        $response->header('Vary', 'Accept-Encoding');

        return $response;
    }

    /**
     * Build Content Security Policy header based on environment
     */
    private function getContentSecurityPolicy(): string
    {
        $isDev = config('app.debug');
        
        // Development policy - allow Vite dev server and Reverb/Pusher
        if ($isDev) {
            return "default-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 http://localhost:5174 https://localhost:5173 https://localhost:5174 http://127.0.0.1:5173 http://127.0.0.1:5174; "
                . "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 http://localhost:5174 https://localhost:5173 https://localhost:5174 http://127.0.0.1:5173 http://127.0.0.1:5174; "
                . "style-src 'self' 'unsafe-inline' http://localhost:5173 http://localhost:5174 https://localhost:5173 https://localhost:5174 http://127.0.0.1:5173 http://127.0.0.1:5174 *.bunny.net; "
                . "img-src 'self' data: https: blob:; "
                . "font-src 'self' *.bunny.net; "
                . "connect-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 http://localhost:5174 https://localhost:5173 https://localhost:5174 http://127.0.0.1:5173 http://127.0.0.1:5174 ws://localhost:5173 ws://localhost:5174 wss://localhost:5173 wss://localhost:5174 ws://127.0.0.1:5173 ws://127.0.0.1:5174 wss://127.0.0.1:5173 wss://127.0.0.1:5174 ws://localhost:8080 wss://localhost:8080 ws://127.0.0.1:8080 wss://127.0.0.1:8080 https://js.pusher.com https://sockjs.pusher.com wss://*.pusher.com;";
        }
        
        // Production policy - strict
        return "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' *.bunny.net; "
            . "style-src 'self' 'unsafe-inline' *.bunny.net; "
            . "img-src 'self' data: https:; "
            . "font-src 'self' *.bunny.net; "
            . "connect-src 'self' *.bunny.net wss://*.pusher.com https://js.pusher.com;";
    }

    /**
     * Check if request is for a static asset
     */
    private function isStaticAsset(Request $request): bool
    {
        return preg_match('/\.(js|css|woff2?|ttf|otf|eot|svg|gif|png|jpg|jpeg|webp|ico)(\?.*)?$/i', $request->getPathInfo());
    }
}
