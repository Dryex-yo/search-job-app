<?php

namespace App\Actions\Jobs;

use App\Models\Job;
use Illuminate\Support\Facades\Cache;

class GetJobDetailsAction
{
    // Cache job details for 1 hour (jobs don't change frequently)
    private const CACHE_DURATION = 3600;

    public function execute(int $id): Job
    {
        // Try to get from cache first
        $cacheKey = "job_details_{$id}";
        
        $job = Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($id) {
            return Job::findOrFail($id);
        });

        return $job;
    }

    /**
     * Invalidate cache when job is updated
     */
    public static function invalidateCache(int $id): void
    {
        Cache::forget("job_details_{$id}");
    }
}