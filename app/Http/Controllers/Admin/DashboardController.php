<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use App\Services\DashboardCacheService;
use App\Actions\Applications\UpdateApplicationStatusAction;
use App\Exports\ApplicationsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    private DashboardCacheService $cacheService;

    public function __construct(DashboardCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard', [
            'user' => Auth::user(),
            'analytics' => $this->getAnalyticsData()
        ]);
    }

    public function index()
    {
        // Check if user has permission to view applicants
        // Allow if user is admin OR has explicit view-applicants permission
        /** @var User $user */
        $user = Auth::user();
        if (!($user->hasRole('admin') || $user->can('view-applicants'))) {
            abort(403, 'Unauthorized to view applicants');
        }

        $applicants = Application::with(['user', 'job'])
            ->latest()
            ->paginate(15)
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'name' => $app->user->name ?? 'Unknown User',
                    'email' => $app->user->email ?? 'N/A',
                    'role' => $app->job->title ?? 'Unknown Role',
                    'job' => [
                        'title' => $app->job->title ?? 'Unknown Role'
                    ],
                    'status' => $app->status, 
                    'date' => $app->created_at->format('d M Y'),
                    'created_at' => $app->created_at,
                    'avatar' => strtoupper(substr($app->user->name ?? '??', 0, 2)),
                    'resume_path' => $app->resume_path,
                    'cover_letter' => $app->cover_letter ?? null,
                    'ai_match_score' => $app->ai_match_score,
                    'ai_analysis_status' => $app->ai_analysis_status,
                    'ai_analysis_details' => $app->ai_analysis_details,
                ];
            });

        return Inertia::render('Admin/Applicants', [
            'applicants' => $applicants
        ]);
    }

    public function update(Request $request, $id, UpdateApplicationStatusAction $updateStatus)
    {
        $request->validate([
            'status' => 'required|in:pending,shortlisted,rejected,interview,hired',
            'notes' => 'nullable|string|min:0|max:5000'
        ]);

        // Verify the application exists and is accessible to the user
        $application = Application::findOrFail((int)$id);

        // Update using the action for status
        $updateStatus->execute($id, $request->status);

        // Also update notes if provided
        if ($request->has('notes') && !empty($request->notes)) {
            $application->update(['notes' => trim($request->notes)]);
        }

        // Invalidate admin caches when status changes
        $this->cacheService->invalidateAllCaches();

        return back()->with('message', 'Status pelamar berhasil diperbarui!');
    }

    /**
     * Show detailed view of a single application
     */
    public function show($id)
    {
        $application = Application::with(['user', 'job'])->findOrFail($id);

        return Inertia::render('Admin/ApplicationDetail', [
            'application' => [
                'id' => $application->id,
                'user_name' => $application->user->name,
                'user_email' => $application->user->email,
                'job_title' => $application->job->title,
                'job_type' => $application->job->type,
                'job_location' => $application->job->location,
                'job_salary' => $application->job->salary,
                'status' => $application->status,
                'cover_letter' => $application->cover_letter,
                'resume_path' => $application->resume_path,
                'notes' => $application->notes ?? '',
                'created_at' => $application->created_at,
            ]
        ]);
    }

    public function downloadResume($id)
    {
        $application = Application::findOrFail($id);
        
        if ($application->resume_path && Storage::disk('public')->exists($application->resume_path)) {
            return response()->download(storage_path('app/public/' . $application->resume_path));
        }

        return back()->with('error', 'Maaf, file resume tidak ditemukan di server.');
    }

    /**
     * Get analytics data for admin pages (cached)
     */
    private function getAnalyticsData()
    {
        $cacheKey = 'admin_dashboard_analytics';

        return Cache::remember($cacheKey, 3600, function () {
            $totalApplications = Application::count();
            $hiredCount = Application::where('status', 'hired')->count();
            $rejectedCount = Application::where('status', 'rejected')->count();
            $shortlistedCount = Application::where('status', 'shortlisted')->count();
            $pendingCount = Application::where('status', 'pending')->count();
            
            $successRate = $totalApplications > 0 ? round(($hiredCount / $totalApplications) * 100, 1) : 0;
            $totalRevenue = $hiredCount * 500;
            $monthlyData = $this->getMonthlyApplicationsData();
            
            return [
                'total_jobs' => Job::count(),
                'total_applications' => $totalApplications,
                'total_users' => User::whereNotIn('id', [Auth::id()])->count(),
                'active_jobs' => Job::where('status', 'active')->count(),
                'pending_applications' => $pendingCount,
                'shortlisted_applications' => $shortlistedCount,
                'rejected_applications' => $rejectedCount,
                'hired_count' => $hiredCount,
                'success_rate' => $successRate,
                'total_revenue' => $totalRevenue,
                'monthly_applications' => $monthlyData,
            ];
        });
    }

    public function analytics()
    {
        return Inertia::render('Admin/Analytics', [
            'analytics' => $this->getAnalyticsData(),
            'chartData' => [
                'weeklyApplicants' => $this->getWeeklyApplicantsData(),
                'statusDistribution' => $this->getStatusDistribution(),
                'monthlyApplications' => $this->getMonthlyApplicationsChart(),
            ],
            'topPerformingJobs' => $this->getTopPerformingJobs(),
        ]);
    }

    public function jobs(Request $request)
    {
        // Validate filter inputs
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
            'type' => 'nullable|in:Full-time,Part-time,Contract,Freelance',
        ]);

        $search = $validated['search'] ?? '';
        $status = $validated['status'] ?? '';
        $type = $validated['type'] ?? '';

        $jobsQuery = Job::query();

        // Search filter
        if ($search) {
            $jobsQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status) {
            $jobsQuery->where('status', $status);
        }

        // Type filter
        if ($type) {
            $jobsQuery->where('type', $type);
        }

        $jobs = $jobsQuery->latest()->paginate(15)->map(function($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'company_name' => $job->company_name,
                'location' => $job->location,
                'salary' => $job->salary,
                'type' => $job->type,
                'status' => $job->status,
                'applications_count' => Application::where('job_id', $job->id)->count(),
                'hired_count' => Application::where('job_id', $job->id)->where('status', 'hired')->count(),
                'shortlisted_count' => Application::where('job_id', $job->id)->where('status', 'shortlisted')->count(),
                'created_at' => $job->created_at->format('d M Y'),
                'created_at_raw' => $job->created_at,
            ];
        });

        return Inertia::render('Admin/Jobs', [
            'analytics' => $this->getAnalyticsData(),
            'jobs' => $jobs,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'type' => $type,
            ]
        ]);
    }

    public function storeJob(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            abort(403, 'Unauthorized to create jobs');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'salary' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|in:Full-time,Part-time,Contract,Freelance',
            'status' => 'required|in:active,inactive',
        ]);

        Job::create($validated);
        $this->cacheService->invalidateAllCaches();

        return back()->with('message', 'Job berhasil ditambahkan!');
    }

    public function updateJob(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            abort(403, 'Unauthorized to update jobs');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'salary' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|in:Full-time,Part-time,Contract,Freelance',
            'status' => 'required|in:active,inactive',
        ]);

        Job::findOrFail($id)->update($validated);
        $this->cacheService->invalidateAllCaches();

        return back()->with('message', 'Job berhasil diperbarui!');
    }

    public function deleteJob($id)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->can('delete-jobs') && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized to delete jobs');
        }

        $job = Job::findOrFail($id);
        
        Application::where('job_id', $id)->delete();
        $job->delete();
        $this->cacheService->invalidateAllCaches();

        return back()->with('message', 'Job berhasil dihapus!');
    }

    public function settings()
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->can('change-settings') && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized to access settings');
        }

        return Inertia::render('Admin/Settings', [
            'analytics' => $this->getAnalyticsData()
        ]);
    }

    /**
     * Get monthly applications data for the last 12 months (cached)
     */
    private function getMonthlyApplicationsData()
    {
        $cacheKey = 'monthly_applications_data';

        return Cache::remember($cacheKey, 86400, function () {
            $monthlyData = [];
            $categories = [];
            
            for ($i = 11; $i >= 0; $i--) {
                $startDate = Carbon::now()->subMonths($i)->startOfMonth();
                $endDate = Carbon::now()->subMonths($i)->endOfMonth();
                
                $count = Application::whereBetween('created_at', [$startDate, $endDate])->count();
                $monthlyData[] = $count;
                $categories[] = $startDate->format('M');
            }
            
            return [
                'data' => $monthlyData,
                'categories' => $categories,
            ];
        });
    }

    /**
     * Get weekly applicants data for line chart (last 12 weeks) (cached)
     */
    private function getWeeklyApplicantsData()
    {
        $cacheKey = 'weekly_applicants_data';

        return Cache::remember($cacheKey, 86400, function () {
            $weeksData = [];
            $categories = [];
            
            for ($i = 11; $i >= 0; $i--) {
                $startDate = Carbon::now()->subWeeks($i)->startOfWeek();
                $endDate = Carbon::now()->subWeeks($i)->endOfWeek();
                
                $count = Application::whereBetween('created_at', [$startDate, $endDate])->count();
                $weeksData[] = $count;
                $categories[] = $startDate->format('d M');
            }
            
            return [
                'series' => [
                    [
                        'name' => 'Aplikasi Per Minggu',
                        'data' => $weeksData
                    ]
                ],
                'categories' => $categories,
            ];
        });
    }

    /**
     * Export all applications to Excel
     */
    public function exportExcel()
    {
        $timestamp = Carbon::now()->format('d_m_Y_H_i_s');
        return Excel::download(new ApplicationsExport, "applicants_report_{$timestamp}.xlsx");
    }

    /**
     * Get status distribution for pie chart (cached)
     */
    private function getStatusDistribution()
    {
        $cacheKey = 'status_distribution_chart';

        return Cache::remember($cacheKey, 3600, function () {
            $hiredCount = Application::where('status', 'hired')->count();
            $rejectedCount = Application::where('status', 'rejected')->count();
            $otherCount = Application::whereNotIn('status', ['hired', 'rejected'])->count();
            
            return [
                'series' => [$hiredCount, $rejectedCount, $otherCount],
                'labels' => ['Hired 🎉', 'Rejected ❌', 'In Progress ⏳'],
                'colors' => ['#10b981', '#ef4444', '#f59e0b'],
            ];
        });
    }

    /**
     * Get monthly applications data for bar chart (cached)
     */
    private function getMonthlyApplicationsChart()
    {
        $cacheKey = 'monthly_applications_chart';

        return Cache::remember($cacheKey, 86400, function () {
            $monthlyData = [];
            $categories = [];
            
            for ($i = 11; $i >= 0; $i--) {
                $startDate = Carbon::now()->subMonths($i)->startOfMonth();
                $endDate = Carbon::now()->subMonths($i)->endOfMonth();
                
                $count = Application::whereBetween('created_at', [$startDate, $endDate])->count();
                $monthlyData[] = $count;
                $categories[] = $startDate->format('M Y');
            }
            
            return [
                'series' => [
                    [
                        'name' => 'Aplikasi',
                        'data' => $monthlyData
                    ]
                ],
                'categories' => $categories,
            ];
        });
    }

    /**
     * Get top 5 performing jobs by applications count (cached)
     */
    private function getTopPerformingJobs()
    {
        $cacheKey = 'top_performing_jobs';

        return Cache::remember($cacheKey, 3600, function () {
            return Job::withCount('applications')
                ->withCount(['applications as hired_count' => function($query) {
                    $query->where('status', 'hired');
                }])
                ->orderByDesc('applications_count')
                ->limit(5)
                ->get()
                ->map(function($job) {
                    $totalApps = $job->applications_count ?? 0;
                    $hiredApps = $job->hired_count ?? 0;
                    $conversionRate = $totalApps > 0 ? round(($hiredApps / $totalApps) * 100, 1) : 0;
                    
                    return [
                        'id' => $job->id,
                        'title' => $job->title,
                        'company_name' => $job->company_name,
                        'applications_count' => $totalApps,
                        'hired_count' => $hiredApps,
                        'conversion_rate' => $conversionRate,
                        'status' => $job->status,
                    ];
                });
        });
    }
}