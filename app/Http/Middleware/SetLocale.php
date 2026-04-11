<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['en', 'id'];
        $locale = null;

        // Priority 1: Check app_locale cookie (encrypted by Laravel's EncryptCookies middleware)
        if ($request->hasCookie('app_locale')) {
            try {
                // Laravel automatically decrypts cookies in $request->cookie()
                $locale = $request->cookie('app_locale');
                
                // Validate locale
                if (!in_array($locale, $supportedLocales)) {
                    $locale = null;
                }
            } catch (\Exception $e) {
                // Decryption failed, continue to next priority
                $locale = null;
            }
        }

        // Priority 2: Check Accept-Language header
        if (!$locale) {
            $acceptLanguage = $request->getPreferredLanguage($supportedLocales);
            if ($acceptLanguage && in_array($acceptLanguage, $supportedLocales)) {
                $locale = $acceptLanguage;
            }
        }

        // Priority 3: Fall back to Indonesian as default
        if (!$locale) {
            $locale = 'id';
        }

        // Set locale in app
        App::setLocale($locale);

        // Store in request for use elsewhere
        $request->attributes->set('app_locale', $locale);

        return $next($request);
    }
}