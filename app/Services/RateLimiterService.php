<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RateLimiterService
{
    private const RATE_LIMIT_PREFIX = 'gemini_rate_limit_';
    
    // Gemini API limits (per minute)
    private const GEMINI_REQUESTS_PER_MINUTE = 60;
    private const GEMINI_TOKENS_PER_MINUTE = 32000;
    
    // OpenAI limits (per minute)
    private const OPENAI_REQUESTS_PER_MINUTE = 90;
    private const OPENAI_TOKENS_PER_MINUTE = 90000;

    /**
     * Check if we can make a request without hitting rate limits
     */
    public function canMakeRequest(string $provider = 'gemini', int $estimatedTokens = 500): bool
    {
        if ($provider === 'gemini') {
            return $this->checkTokenBucket(
                'gemini',
                self::GEMINI_REQUESTS_PER_MINUTE,
                self::GEMINI_TOKENS_PER_MINUTE,
                $estimatedTokens
            );
        } elseif ($provider === 'openai') {
            return $this->checkTokenBucket(
                'openai',
                self::OPENAI_REQUESTS_PER_MINUTE,
                self::OPENAI_TOKENS_PER_MINUTE,
                $estimatedTokens
            );
        }

        return true;
    }

    /**
     * Get wait time (in seconds) before next request
     */
    public function getWaitTime(string $provider = 'gemini'): int
    {
        $key = self::RATE_LIMIT_PREFIX . $provider . '_wait_until';
        $waitUntil = Cache::get($key, 0);
        $now = time();

        return max(0, $waitUntil - $now);
    }

    /**
     * Record a successful request
     */
    public function recordRequest(string $provider = 'gemini', int $tokensUsed = 0): void
    {
        $requestKey = self::RATE_LIMIT_PREFIX . $provider . '_requests';
        $tokensKey = self::RATE_LIMIT_PREFIX . $provider . '_tokens';

        $requests = Cache::get($requestKey, 0) + 1;
        $tokens = Cache::get($tokensKey, 0) + $tokensUsed;

        Cache::put($requestKey, $requests, 60);
        Cache::put($tokensKey, $tokens, 60);
    }

    /**
     * Record rate limit error and set backoff
     */
    public function recordRateLimit(string $provider = 'gemini', int $backoffSeconds = 60): void
    {
        $key = self::RATE_LIMIT_PREFIX . $provider . '_wait_until';
        $waitUntil = time() + $backoffSeconds;

        Cache::put($key, $waitUntil, $backoffSeconds + 10);

        \Illuminate\Support\Facades\Log::warning("Rate limit recorded for $provider", [
            'backoff_seconds' => $backoffSeconds,
            'wait_until' => $waitUntil
        ]);
    }

    /**
     * Get current usage stats
     */
    public function getStats(string $provider = 'gemini'): array
    {
        $requestKey = self::RATE_LIMIT_PREFIX . $provider . '_requests';
        $tokensKey = self::RATE_LIMIT_PREFIX . $provider . '_tokens';
        $waitKey = self::RATE_LIMIT_PREFIX . $provider . '_wait_until';

        $requests = Cache::get($requestKey, 0);
        $tokens = Cache::get($tokensKey, 0);
        $waitUntil = Cache::get($waitKey, 0);

        if ($provider === 'gemini') {
            $limitRequests = self::GEMINI_REQUESTS_PER_MINUTE;
            $limitTokens = self::GEMINI_TOKENS_PER_MINUTE;
        } else {
            $limitRequests = self::OPENAI_REQUESTS_PER_MINUTE;
            $limitTokens = self::OPENAI_TOKENS_PER_MINUTE;
        }

        return [
            'provider' => $provider,
            'requests_used' => $requests,
            'requests_limit' => $limitRequests,
            'requests_remaining' => max(0, $limitRequests - $requests),
            'tokens_used' => $tokens,
            'tokens_limit' => $limitTokens,
            'tokens_remaining' => max(0, $limitTokens - $tokens),
            'wait_seconds' => max(0, $waitUntil - time()),
            'timestamp' => now()->toIso8601String()
        ];
    }

    /**
     * Reset all rate limit counters
     */
    public function reset(string $provider = 'gemini'): void
    {
        $requestKey = self::RATE_LIMIT_PREFIX . $provider . '_requests';
        $tokensKey = self::RATE_LIMIT_PREFIX . $provider . '_tokens';
        $waitKey = self::RATE_LIMIT_PREFIX . $provider . '_wait_until';

        Cache::forget($requestKey);
        Cache::forget($tokensKey);
        Cache::forget($waitKey);

        \Illuminate\Support\Facades\Log::info("Rate limiter reset for $provider");
    }

    /**
     * Implement token bucket algorithm
     */
    private function checkTokenBucket(string $provider, int $requestLimit, int $tokenLimit, int $estimatedTokens): bool
    {
        $requestKey = self::RATE_LIMIT_PREFIX . $provider . '_requests';
        $tokensKey = self::RATE_LIMIT_PREFIX . $provider . '_tokens';

        $requests = Cache::get($requestKey, 0);
        $tokens = Cache::get($tokensKey, 0);

        $canRequest = $requests < $requestLimit && $tokens + $estimatedTokens <= $tokenLimit;

        if (!$canRequest) {
            \Illuminate\Support\Facades\Log::warning("Rate limit approaching for $provider", [
                'requests' => $requests,
                'request_limit' => $requestLimit,
                'tokens' => $tokens,
                'token_limit' => $tokenLimit,
                'estimated_tokens' => $estimatedTokens
            ]);
        }

        return $canRequest;
    }
}
