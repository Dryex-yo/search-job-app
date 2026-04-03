<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Job;
use App\Models\ProfileView;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardCacheService
{
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get cached dashboard data for a user
     */
    public function getDashboardData($user, $forceRefresh = false)
    {
        $cacheKey = "dashboard_data_{$user->id}";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return $this->buildDashboardData($user);
        });
    }

    /**
     * Build complete dashboard data
     */
    private function buildDashboardData($user)
    {
        $userId = $user->id;

        // Get user applications with eager loading
        $userApplications = Application::where('user_id', $userId)
            ->with('job')
            ->latest()
            ->get();

        // Calculate statistics
        $statusCounts = $userApplications->countBy('status');
        $totalApplications = $userApplications->count();

        // Get recent applications (last 5)
        $recentApplications = $userApplications->take(5)->map(function ($app) {
            return [
                'id' => $app->id,
                'job_title' => $app->job->title,
                'company_name' => $app->job->company_name,
                'status' => $app->status,
                'salary' => $app->job->salary,
                'job_type' => $app->job->type,
                'created_at' => $app->created_at->format('M d, Y'),
                'created_at_raw' => $app->created_at,
            ];
        });

        // Get recommended jobs (active jobs that user hasn't applied to yet)
        $appliedJobIds = $userApplications->pluck('job_id')->toArray();
        $recommendedJobs = Job::where('status', 'active')
            ->whereNotIn('id', $appliedJobIds)
            ->latest()
            ->limit(6)
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'company_name' => $job->company_name,
                    'location' => $job->location,
                    'salary' => $job->salary,
                    'type' => $job->type,
                    'applications_count' => $job->applications()->count(),
                ];
            });

        // Get job categories/types from user's applications
        $jobCategories = $userApplications->groupBy(function ($app) {
            return $app->job->type;
        })->map(function ($apps, $type) {
            return [
                'type' => $type,
                'count' => $apps->count(),
            ];
        })->values();

        // Get profile metrics
        $profileViews = ProfileView::where('user_id', $userId)->count();
        $profileViewsThisMonth = ProfileView::where('user_id', $userId)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        // Calculate this month applications
        $thisMonthApplications = $userApplications->filter(function ($app) {
            return $app->created_at->month === Carbon::now()->month &&
                   $app->created_at->year === Carbon::now()->year;
        })->count();

        // Get application status trend (cached separately)
        $statusTrend = $this->getCachedStatusTrend($userId);

        return [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'statistics' => [
                'total_applications' => $totalApplications,
                'pending_applications' => $statusCounts->get('pending', 0),
                'shortlisted_applications' => $statusCounts->get('shortlisted', 0),
                'interview_applications' => $statusCounts->get('interview', 0),
                'hired_applications' => $statusCounts->get('hired', 0),
                'rejected_applications' => $statusCounts->get('rejected', 0),
                'this_month_applications' => $thisMonthApplications,
                'profile_views' => $profileViews,
                'profile_views_this_month' => $profileViewsThisMonth,
            ],
            'recentApplications' => $recentApplications->toArray(),
            'recommendedJobs' => $recommendedJobs->toArray(),
            'statusTrend' => $statusTrend,
            'jobCategories' => $jobCategories->toArray(),
        ];
    }

    /**
     * Get application status trend for last 7 days (cached)
     */
    public function getCachedStatusTrend($userId)
    {
        $cacheKey = "status_trend_{$userId}";

        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            $trend = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $count = Application::where('user_id', $userId)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->count();

                $trend[] = [
                    'date' => $date->format('M d'),
                    'count' => $count,
                ];
            }

            return $trend;
        });
    }

    /**
     * Invalidate dashboard cache for a user
     */
    public function invalidateCache($userId)
    {
        Cache::forget("dashboard_data_{$userId}");
        Cache::forget("status_trend_{$userId}");
    }

    /**
     * Get cached analytics data for admin dashboard
     */
    public function getAdminAnalytics($forceRefresh = false)
    {
        $cacheKey = 'admin_dashboard_analytics';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return [
                'total_jobs' => Job::count(),
                'total_applications' => Application::count(),
                'total_users' => \App\Models\User::count(),
                'active_jobs' => Job::where('status', 'active')->count(),
                'hired_count' => Application::where('status', 'hired')->count(),
                'rejected_count' => Application::where('status', 'rejected')->count(),
                'shortlisted_count' => Application::where('status', 'shortlisted')->count(),
                'pending_count' => Application::where('status', 'pending')->count(),
            ];
        });
    }

    /**
     * Get monthly applications data for admin (cached)
     */
    public function getMonthlyApplicationsData()
    {
        $cacheKey = 'monthly_applications_data';

        return Cache::remember($cacheKey, 86400, function () { // Cache for 24 hours
            $monthlyData = [];

            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $count = Application::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();

                $monthlyData[] = [
                    'month' => $month->format('M'),
                    'count' => $count,
                ];
            }

            return $monthlyData;
        });
    }

    /**
     * Invalidate all dashboard caches
     */
    public function invalidateAllCaches()
    {
        Cache::forget('admin_dashboard_analytics');
        Cache::forget('monthly_applications_data');
    }
}
