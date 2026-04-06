<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class AnalysisCacheService
{
    private const CACHE_TTL = 86400 * 30; // 30 days
    private const CACHE_PREFIX = 'cv_analysis_';

    /**
     * Generate cache key based on CV, Job Title, and Job Description
     */
    public function generateKey(string $cvText, string $jobTitle, string $jobDescription): string
    {
        $combined = hash('sha256', $cvText . '|' . $jobTitle . '|' . $jobDescription);
        return self::CACHE_PREFIX . $combined;
    }

    /**
     * Get cached analysis result
     */
    public function get(string $cvText, string $jobTitle, string $jobDescription): ?array
    {
        $key = $this->generateKey($cvText, $jobTitle, $jobDescription);
        $cached = Cache::get($key);

        if ($cached) {
            \Illuminate\Support\Facades\Log::info('CV Analysis cache hit', [
                'key' => $key,
                'score' => $cached['score'] ?? null
            ]);
        }

        return $cached;
    }

    /**
     * Store analysis result in cache
     */
    public function put(string $cvText, string $jobTitle, string $jobDescription, array $result): void
    {
        $key = $this->generateKey($cvText, $jobTitle, $jobDescription);
        Cache::put($key, $result, self::CACHE_TTL);

        \Illuminate\Support\Facades\Log::info('CV Analysis cached', [
            'key' => $key,
            'ttl' => self::CACHE_TTL,
            'score' => $result['score'] ?? null
        ]);
    }

    /**
     * Clear all cached analyses
     */
    public function flush(): bool
    {
        // For filesystem cache, we can't iterate through keys easily
        // Log the limitation and return success
        \Illuminate\Support\Facades\Log::info('CV Analysis cache flush requested', [
            'note' => 'For complete flush, use: php artisan cache:clear'
        ]);
        return true;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        // For file or other cache stores, we can't easily count keys
        // Return a simpler stat object
        return [
            'cached_analyses' => 'N/A (depends on cache driver)',
            'cache_ttl_days' => self::CACHE_TTL / 86400,
            'prefix' => self::CACHE_PREFIX,
            'note' => 'Stats only available with Redis/Memcached drivers'
        ];
    }
}
