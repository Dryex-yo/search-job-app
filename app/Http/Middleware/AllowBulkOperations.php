<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware untuk memungkinkan operasi bulk yang mengandung array parameter
 * Harus dijalankan SEBELUM PreventSqlInjection middleware
 */
class AllowBulkOperations
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
        // List operasi bulk yang dibolehkan
        $allowedBulkPaths = [
            '/admin/applications/bulk-update',
            'admin/applications/bulk-update',
        ];

        $currentPath = $request->path();

        // Jika ini bulk operation, mark request agar PreventSqlInjection skip validation
        if (in_array($currentPath, $allowedBulkPaths) || str_contains($currentPath, '/admin/applications/bulk-update')) {
            // Set custom attribute untuk signal ke middleware lain
            $request->attributes->set('skip_sql_injection_check', true);
            
            \Log::info('✓ Bulk operation detected and whitelisted', [
                'path' => $currentPath,
                'method' => $request->method(),
                'marked_for_skip' => true,
            ]);
        }

        return $next($request);
    }
}
