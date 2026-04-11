<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    /**
     * Set application locale
     */
    public function set(Request $request, $locale)
    {
        $supportedLocales = ['en', 'id'];

        // Validate locale
        if (!in_array($locale, $supportedLocales)) {
            return response()->json(['success' => false, 'message' => 'Invalid locale'], 400);
        }

        // Set locale in app
        App::setLocale($locale);

        // Create response
        $response = response()->json(['success' => true, 'locale' => $locale]);

        // Set plain text cookie directly via header to bypass EncryptCookies middleware
        // This way app_locale won't be encrypted by Laravel's EncryptCookies
        $cookieValue = $locale . '; path=/; Max-Age=' . (60 * 24 * 365) . '; HttpOnly; SameSite=Lax';
        $response->header('Set-Cookie', 'app_locale=' . $cookieValue);

        return $response;
    }
}
