<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PreventSqlInjection
{
    /**
     * SQL Injection attack patterns
     * 
     * @var array
     */
    protected array $sqlInjectionPatterns = [
        // Basic SQL commands
        "/(union|select|insert|update|delete|drop|create|alter|exec|execute|script|javascript|onclick|onload|onerror|fetch|xmlhttp|eval|alert|confirm|prompt)/i",
        
        // SQL comments
        "/(\-\-|\/\*|\*\/|;|\||&&)/",
        
        // SQL keywords combined with operators
        "/\b(or|and)\b\s*(=|!=|<|>|<=|>=|like)\s*['\"]/i",
        
        // Hex encoding
        "/0x[0-9a-f]+/i",
        
        // Attempts to break quotes
        "/(\'|\")(\s|\.|\+)*(or|and|union|select)(\s|\.|\+)*(\'|\")/i",
        
        // Command execution patterns
        "/(`|%60|\$\(|\$|`)(.*)(cmd|powershell|bash|sh|exec)/i",
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if this request has been marked to skip SQL injection check (by AllowBulkOperations middleware)
        if ($request->attributes->get('skip_sql_injection_check', false)) {
            \Log::info('⊘ Skipping PreventSqlInjection - bulk operation whitelisted');
            return $next($request);
        }

        // Skip SQL injection check for file upload routes, profile updates, and bulk operations
        // Use multiple strategies to identify exception routes:
        
        // 1. By route name (if available)
        $excludedRoutes = [
            'profile.upload-photo',
            'profile.upload-resume',
            'profile.update',
            'admin.applications.bulk-update',
        ];

        // 2. By path pattern
        $currentPath = $request->path();
        $excludedPaths = [
            '/admin/applications/bulk-update',
            'admin/applications/bulk-update',  // Without leading slash
        ];

        // 3. By method + path combination
        $excludedOperations = [
            'PATCH|/admin/applications/bulk-update',
            'PATCH|admin/applications/bulk-update',
        ];

        $currentRoute = $request->route()?->getName();
        $currentMethod = $request->method();
        $operation = $currentMethod . '|' . $currentPath;

        // Check if request should be excluded
        $isExcluded = 
            in_array($currentRoute, $excludedRoutes) ||
            in_array($currentPath, $excludedPaths) ||
            in_array($operation, $excludedOperations) ||
            str_contains($currentPath, '/admin/applications/bulk-update');

        // Enhanced logging
        if (str_contains($currentPath, 'bulk') || str_contains($currentRoute, 'bulk')) {
            \Log::warning('↳ BULK REQUEST - PreventSqlInjection Check', [
                'route_name' => $currentRoute,
                'path' => $currentPath,
                'method' => $currentMethod,
                'operation' => $operation,
                'is_excluded' => $isExcluded,
                'decision' => $isExcluded ? 'SKIP VALIDATION' : 'WILL VALIDATE',
            ]);
        }

        if (!$isExcluded) {
            // Check all request parameters for SQL injection patterns
            $this->validateRequestParameters($request);
        }

        return $next($request);
    }

    /**
     * Validate request parameters against SQL injection patterns
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function validateRequestParameters(Request $request): void
    {
        $allInputs = array_merge(
            $request->query(),
            $request->post(),
            $request->route()?->parameters() ?? []
        );

        // Log all inputs if this might be a bulk operation (for debugging)
        if (count($allInputs) > 0 && strpos($request->path(), 'bulk') !== false) {
            \Log::warning('DEBUG: Validating params for bulk request', [
                'path' => $request->path(),
                'total_params' => count($allInputs),
                'param_keys' => array_keys($allInputs),
            ]);
        }

        foreach ($allInputs as $key => $value) {
            if (is_string($value)) {
                $this->checkForSqlInjection($key, $value);
            } elseif (is_array($value)) {
                $this->checkArrayForSqlInjection($key, $value);
            }
        }
    }

    /**
     * Check array values for SQL injection
     *
     * @param  string  $parentKey
     * @param  array  $values
     * @return void
     */
    protected function checkArrayForSqlInjection(string $parentKey, array $values): void
    {
        // Skip checking numeric arrays (like application_ids)
        $isNumericArray = true;
        foreach ($values as $value) {
            if (!is_numeric($value)) {
                $isNumericArray = false;
                break;
            }
        }
        
        // If array contains only numeric values, skip SQL injection check
        if ($isNumericArray) {
            return;
        }
        
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $this->checkForSqlInjection("{$parentKey}.{$key}", $value);
            } elseif (is_array($value)) {
                $this->checkArrayForSqlInjection("{$parentKey}.{$key}", $value);
            }
        }
    }

    /**
     * Check a single value for SQL injection patterns
     *
     * @param  string  $key
     * @param  string  $value
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function checkForSqlInjection(string $key, string $value): void
    {
        // Skip empty values
        if (empty($value)) {
            return;
        }

        // Check each SQL injection pattern
        foreach ($this->sqlInjectionPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                // Log the suspected injection attempt with full details
                \Log::error('⚠️ SQL INJECTION BLOCKED', [
                    'parameter' => $key,
                    'value' => substr($value, 0, 100),
                    'value_length' => strlen($value),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'url' => request()->url(),
                    'path' => request()->path(),
                    'method' => request()->method(),
                    'matched_pattern_index' => array_search($pattern, $this->sqlInjectionPatterns),
                ]);

                // Return 403 Forbidden response
                abort(403, 'Suspicious input detected. Your request has been blocked.');
            }
        }
    }
}
