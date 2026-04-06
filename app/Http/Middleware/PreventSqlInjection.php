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
        // Skip SQL injection check for file upload routes and profile updates
        $excludedRoutes = [
            'profile.upload-photo',
            'profile.upload-resume',
            'profile.update',
        ];

        if (!in_array($request->route()?->getName(), $excludedRoutes)) {
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
                // Log the suspected injection attempt
                Log::warning('Potential SQL Injection detected', [
                    'parameter' => $key,
                    'value' => substr($value, 0, 100),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'url' => request()->url(),
                ]);

                // Return 403 Forbidden response
                abort(403, 'Suspicious input detected. Your request has been blocked.');
            }
        }
    }
}
