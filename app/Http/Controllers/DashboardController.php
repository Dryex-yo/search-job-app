<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\ProfileView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show dashboard based on user role
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Redirect admin to admin dashboard
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Show user dashboard for regular users
        return Inertia::render('Dashboard', [
            'dashboardData' => $this->getDashboardData($user),
        ]);
    }

    /**
     * Get comprehensive dashboard data for authenticated user
     */
    private function getDashboardData($user)
    {
        $userId = $user->id;

        // Get user applications
        $userApplications = Application::where('user_id', $userId)
            ->with('job')
            ->latest()
            ->get();

        // Calculate statistics
        $totalApplications = $userApplications->count();
        $pendingCount = $userApplications->where('status', 'pending')->count();
        $shortlistedCount = $userApplications->where('status', 'shortlisted')->count();
        $interviewCount = $userApplications->where('status', 'interview')->count();
        $hiredCount = $userApplications->where('status', 'hired')->count();
        $rejectedCount = $userApplications->where('status', 'rejected')->count();

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

        // Calculate profile completion percentage (estimated)
        $profileCompletion = $this->calculateProfileCompletion($user);

        // Get statistics for this month
        $thisMonthApplications = $userApplications->filter(function ($app) {
            return $app->created_at->month === Carbon::now()->month &&
                   $app->created_at->year === Carbon::now()->year;
        })->count();

        // Get application status trend (last 7 days)
        $statusTrend = $this->getApplicationStatusTrend($userId);

        // Get job categories/types from user's applications
        $jobCategories = $userApplications->groupBy(function ($app) {
            return $app->job->type;
        })->map(function ($apps, $type) {
            return [
                'type' => $type,
                'count' => $apps->count(),
            ];
        })->values();

        // Calculate profile metrics from real data
        $profileViews = ProfileView::where('user_id', $userId)->count();
        $profileViewsThisMonth = ProfileView::where('user_id', $userId)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
        $interviewsScheduled = $interviewCount;

        return [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'statistics' => [
                'total_applications' => $totalApplications,
                'pending_applications' => $pendingCount,
                'shortlisted_applications' => $shortlistedCount,
                'interview_applications' => $interviewCount,
                'hired_applications' => $hiredCount,
                'rejected_applications' => $rejectedCount,
                'this_month_applications' => $thisMonthApplications,
                'profile_completion' => $profileCompletion,
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
     * Calculate profile completion based on real profile data
     */
    private function calculateProfileCompletion($user)
    {
        $completion = 0;
        $totalFields = 5; // Total profile fields
        $completedFields = 0;

        // 1. Name completion (20%)
        if ($user->name && strlen($user->name) > 0) {
            $completedFields++;
        }

        // 2. Email verification (20%)
        if ($user->email_verified_at) {
            $completedFields++;
        }

        // 3. Phone number (20%)
        if ($user->phone && strlen($user->phone) > 0) {
            $completedFields++;
        }

        // 4. Bio/Profile summary (20%)
        if ($user->bio && strlen($user->bio) > 0) {
            $completedFields++;
        }

        // 5. Resume uploaded (20%)
        if ($user->resume_path && strlen($user->resume_path) > 0) {
            $completedFields++;
        }

        // Calculate percentage
        $completion = ($completedFields / $totalFields) * 100;

        return min(round($completion), 100);
    }

    /**
     * Get application status trend for last 7 days
     */
    private function getApplicationStatusTrend($userId)
    {
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
    }
}
