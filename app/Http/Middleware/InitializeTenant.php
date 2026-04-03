<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Facades\Tenancy;

class InitializeTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // The tenant finder will automatically identify the tenant from the domain
        // and Spatie will set it in the context through its own middleware
        
        return $next($request);
    }
}
