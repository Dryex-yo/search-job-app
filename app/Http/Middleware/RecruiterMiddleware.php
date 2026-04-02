<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RecruiterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        // Allow admin and recruiter roles
        if ($user && ($user->hasRole(['admin', 'recruiter']) || in_array($user->role, ['admin', 'recruiter']))) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized. Admin or Recruiter access required.'], 403);
    }
}
