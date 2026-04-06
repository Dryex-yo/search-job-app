<?php

namespace App\Services;

/**
 * Optimization statistics helper
 */
class OptimizationStatsService
{
    private static array $stats = [
        'cache_hits' => 0,
        'cache_misses' => 0,
        'api_calls' => 0,
        'rate_limit_checks' => 0,
        'fallback_scores' => 0,
    ];

    public static function recordCacheHit(): void
    {
        self::$stats['cache_hits']++;
    }

    public static function recordCacheMiss(): void
    {
        self::$stats['cache_misses']++;
    }

    public static function recordApiCall(): void
    {
        self::$stats['api_calls']++;
    }

    public static function recordRateLimitCheck(): void
    {
        self::$stats['rate_limit_checks']++;
    }

    public static function recordFallbackScore(): void
    {
        self::$stats['fallback_scores']++;
    }

    public static function getStats(): array
    {
        return self::$stats;
    }

    public static function reset(): void
    {
        self::$stats = [
            'cache_hits' => 0,
            'cache_misses' => 0,
            'api_calls' => 0,
            'rate_limit_checks' => 0,
            'fallback_scores' => 0,
        ];
    }

    public static function getSummary(): string
    {
        $total = self::$stats['cache_hits'] + self::$stats['cache_misses'];
        $hitRate = $total > 0 ? round((self::$stats['cache_hits'] / $total) * 100) : 0;

        $output = "📊 Optimization Statistics:\n";
        $output .= "   • Cache Hits: " . self::$stats['cache_hits'] . "\n";
        $output .= "   • Cache Misses: " . self::$stats['cache_misses'] . "\n";
        $output .= "   • Cache Hit Rate: " . $hitRate . "%\n";
        $output .= "   • API Calls Made: " . self::$stats['api_calls'] . "\n";
        $output .= "   • Rate Limit Checks: " . self::$stats['rate_limit_checks'] . "\n";
        $output .= "   • Fallback Scores: " . self::$stats['fallback_scores'];

        return $output;
    }
}
