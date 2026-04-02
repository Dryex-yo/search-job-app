<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Database Security Service
 * 
 * Provides additional security measures for database operations
 */
class DatabaseSecurityService
{
    /**
     * Enable strict mode for database connections
     * This prevents certain SQL injection techniques
     * 
     * @return void
     */
    public static function enableStrictMode(): void
    {
        try {
            // For MySQL
            DB::statement("SET sql_mode='STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
            Log::info('Database strict mode enabled');
        } catch (\Exception $e) {
            Log::warning('Could not enable strict mode: ' . $e->getMessage());
        }
    }

    /**
     * Validate and sanitize SQL search parameters
     * Used in search queries to prevent SQL injection
     * 
     * @param  string  $searchTerm
     * @param  int  $maxLength
     * @return string
     */
    public static function sanitizeSearchTerm(string $searchTerm, int $maxLength = 255): string
    {
        // Trim whitespace
        $sanitized = trim($searchTerm);

        // Limit length to prevent DOS attacks
        $sanitized = substr($sanitized, 0, $maxLength);

        // Remove control characters
        $sanitized = preg_replace('/[\x00-\x1F\x7F]/u', '', $sanitized);

        return $sanitized;
    }

    /**
     * Validate that a value matches expected pattern
     * Useful for validating IDs and other structured data
     * 
     * @param  mixed  $value
     * @param  string  $pattern
     * @return bool
     */
    public static function validatePattern($value, string $pattern): bool
    {
        return (bool) preg_match($pattern, (string) $value);
    }

    /**
     * Validate numeric ID to prevent injection through ID parameters
     * 
     * @param  mixed  $id
     * @return bool
     */
    public static function validateNumericId($id): bool
    {
        return self::validatePattern($id, '/^\d+$/');
    }

    /**
     * Log suspicious query activity
     * 
     * @param  string  $query
     * @param  array  $bindings
     * @param  string  $reason
     * @return void
     */
    public static function logSuspiciousQuery(string $query, array $bindings = [], string $reason = ''): void
    {
        Log::warning('Potentially suspicious database query detected', [
            'query' => substr($query, 0, 200),
            'bindings_count' => count($bindings),
            'reason' => $reason,
            'ip' => request()->ip(),
            'user_id' => optional(Auth::user())->id,
        ]);
    }

    /**
     * Use parameterized query (This is the recommended way)
     * 
     * @param  string  $query
     * @param  array  $params
     * @return \Illuminate\Database\Query\Builder
     */
    public static function executeParameterizedQuery(string $query, array $params = [])
    {
        return DB::select($query, $params);
    }
}
