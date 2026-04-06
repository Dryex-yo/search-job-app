<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;

class EnsureTenantContextMiddleware
{
    /**
     * Ensure tenant context is properly initialized for authenticated users
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = Auth::id();
        $isAuth = Auth::check();
        
        // Skip tenant initialization for unauthenticated public requests
        if (!$isAuth && str_starts_with($request->path(), 'jobs')) {
            Log::debug('EnsureTenantContextMiddleware - Skipping public job list', [
                'path' => $request->path(),
            ]);
            return $next($request);
        }
        
        Log::debug('EnsureTenantContextMiddleware - Start', [
            'is_authenticated' => $isAuth,
            'user_id' => $userId,
            'request_host' => $request->getHost(),
            'request_path' => $request->path(),
        ]);

        try {
            // Check current tenant context from app container
            $currentTenant = Tenant::current();

            Log::debug('EnsureTenantContextMiddleware - Current tenant check', [
                'has_tenant' => $currentTenant ? true : false,
                'tenant_id' => $currentTenant?->id,
            ]);

            // If no tenant and user is authenticated, restore from user
            if (!$currentTenant && $isAuth && $userId) {
                $user = Auth::user();
                $userTenantId = $user?->tenant_id;

                Log::info('EnsureTenantContextMiddleware - User tenant check', [
                    'user_id' => $userId,
                    'user_tenant_id_from_model' => $userTenantId,
                ]);

                // Check database directly as fallback
                if (!$userTenantId) {
                    $dbUser = DB::table('users')->where('id', $userId)->first();
                    $userTenantId = $dbUser?->tenant_id;
                    
                    Log::warning('EnsureTenantContextMiddleware - Model tenant_id NULL, checked database', [
                        'user_id' => $userId,
                        'db_tenant_id' => $userTenantId,
                    ]);
                }

                // If we found a tenant_id, initialize context
                if ($userTenantId) {
                    try {
                        $tenant = Tenant::find($userTenantId);
                        
                        if ($tenant) {
                            // Initialize tenant using makeCurrent
                            $tenant->makeCurrent();
                            
                            Log::info('EnsureTenantContextMiddleware - Tenant initialized successfully', [
                                'tenant_id' => $tenant->id,
                                'user_id' => $userId,
                            ]);
                        } else {
                            Log::warning('EnsureTenantContextMiddleware - Tenant not found in DB', [
                                'tenant_id' => $userTenantId,
                                'user_id' => $userId,
                            ]);

                            // Last resort: use first tenant for development
                            $firstTenant = Tenant::first();
                            if ($firstTenant) {
                                $firstTenant->makeCurrent();
                                Log::warning('EnsureTenantContextMiddleware - Using first tenant (DB lookup failed)', [
                                    'tenant_id' => $firstTenant->id,
                                    'user_id' => $userId,
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('EnsureTenantContextMiddleware - Failed to initialize tenant: ' . $e->getMessage(), [
                            'tenant_id' => $userTenantId,
                            'user_id' => $userId,
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                } else {
                    // Last resort: use first tenant for development
                    $firstTenant = Tenant::first();
                    if ($firstTenant) {
                        try {
                            $firstTenant->makeCurrent();
                            Log::warning('EnsureTenantContextMiddleware - Using first tenant as fallback (no user tenant)', [
                                'tenant_id' => $firstTenant->id,
                                'user_id' => $userId,
                            ]);
                        } catch (\Exception $e) {
                            Log::error('EnsureTenantContextMiddleware - Failed to set first tenant: ' . $e->getMessage());
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('EnsureTenantContextMiddleware - Unexpected error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $next($request);
    }
}
