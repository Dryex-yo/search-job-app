<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use App\Models\Settings;
use App\Services\DashboardCacheService;
use App\Actions\Applications\UpdateApplicationStatusAction;
use App\Exports\ApplicationsExport;
use App\Exports\ApplicationsExportWithFilters;
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

    public function index(Request $request)
    {
        // Check if user has permission to view applicants
        // Allow if user is admin OR has explicit view-applicants permission
        /** @var User $user */
        $user = Auth::user();
        if (!($user->hasRole('admin') || $user->can('view-applicants'))) {
            abort(403, 'Unauthorized to view applicants');
        }

        // Validate filter inputs
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,shortlisted,rejected,interview,hired',
            'score_min' => 'nullable|numeric|min:0|max:100',
            'score_max' => 'nullable|numeric|min:0|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'sort' => 'nullable|in:latest,oldest,score_high,score_low',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        $query = Application::with(['user', 'job']);

        // Search filter
        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('job', function($subQ) use ($search) {
                    $subQ->where('title', 'like', "%{$search}%");
                });
            });
        }

        // Status filter
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // AI Score range filter
        if (!empty($validated['score_min'])) {
            $query->where('ai_match_score', '>=', $validated['score_min']);
        }
        if (!empty($validated['score_max'])) {
            $query->where('ai_match_score', '<=', $validated['score_max']);
        }

        // Date range filter
        if (!empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        // Sorting
        $sortBy = $validated['sort'] ?? 'latest';
        if ($sortBy === 'oldest') {
            $query->oldest();
        } elseif ($sortBy === 'score_high') {
            $query->orderByDesc('ai_match_score');
        } elseif ($sortBy === 'score_low') {
            $query->orderBy('ai_match_score');
        } else {
            $query->latest();
        }

        // Per page setting
        $perPage = $validated['per_page'] ?? 15;

        $applicants = $query->paginate($perPage)->through(function ($app) {
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
            'applicants' => $applicants,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'status' => $validated['status'] ?? '',
                'score_min' => $validated['score_min'] ?? '',
                'score_max' => $validated['score_max'] ?? '',
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
                'sort' => $sortBy,
                'per_page' => $perPage,
            ]
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
     * Bulk update multiple applications status
     */
    public function bulkUpdate(Request $request, UpdateApplicationStatusAction $updateStatus)
    {
        // Validate input strictly
        $request->validate([
            'application_ids' => 'required|array|min:1|max:100',
            'application_ids.*' => 'integer|min:1',
            'status' => 'required|string|in:pending,shortlisted,rejected,interview,hired',
        ]);

        try {
            // Get the current authenticated user
            /** @var User $user */
            $user = Auth::user();
            
            // Check if user has permission
            if (!($user->hasRole('admin') || $user->can('update-applicants'))) {
                abort(403, 'Unauthorized to update applications');
            }

            $applicationIds = $request->input('application_ids');
            $newStatus = $request->input('status');
            
            // Validate all IDs exist in database before proceeding
            $existingIds = Application::whereIn('id', $applicationIds)->pluck('id')->toArray();
            $invalidIds = array_diff($applicationIds, $existingIds);
            
            if (!empty($invalidIds)) {
                return back()->withErrors([
                    'error' => 'Some applications not found: ' . implode(', ', $invalidIds)
                ]);
            }
            
            // Bulk update all applications
            $updateCount = 0;
            DB::transaction(function () use ($applicationIds, $newStatus, $updateStatus, &$updateCount) {
                foreach ($applicationIds as $id) {
                    // Verify application exists and update using the action for status
                    $updateStatus->execute($id, $newStatus);
                    $updateCount++;
                }
            });

            // Invalidate admin caches when status changes
            $this->cacheService->invalidateAllCaches();

            $statusLabel = ucfirst($newStatus);

            return redirect()->route('admin.applicants')->with(
                'message', 
                "{$updateCount} pelamar berhasil diupdate menjadi {$statusLabel}!"
            );
        } catch (\Throwable $e) {
            \Log::error('Bulk update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
            
            return back()->withErrors([
                'error' => 'Gagal melakukan bulk update. ' . $e->getMessage()
            ]);
        }
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
                // Interview scheduling fields
                'interview_scheduled_at' => $application->interview_scheduled_at,
                'interview_duration_minutes' => $application->interview_duration_minutes ?? 60,
                'interview_type' => $application->interview_type,
                'interview_meeting_link' => $application->interview_meeting_link,
                'interview_meeting_provider' => $application->interview_meeting_provider,
                'interview_notes' => $application->interview_notes,
                'interview_cancelled_at' => $application->interview_cancelled_at,
                // AI Analysis fields
                'ai_match_score' => $application->ai_match_score,
                'ai_analysis_status' => $application->ai_analysis_status,
                'ai_analysis_details' => $application->ai_analysis_details,
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
    public function getAnalyticsDataPublic()
    {
        return $this->getAnalyticsData();
    }

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
            
            // Use Settings model for hiring fee instead of hardcoded 500
            $hiringFee = Settings::get('hiring_fee_per_person', 500.00);
            $totalRevenue = $hiredCount * $hiringFee;
            
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
            'per_page' => 'nullable|integer|min:5|max:100',
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

        // Per page setting
        $perPage = $validated['per_page'] ?? 15;

        $jobs = $jobsQuery
            ->withCount('applications')
            ->withCount(['applications as hired_count' => function($query) {
                $query->where('status', 'hired');
            }])
            ->withCount(['applications as shortlisted_count' => function($query) {
                $query->where('status', 'shortlisted');
            }])
            ->latest()
            ->paginate($perPage)
            ->through(function($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'company_name' => $job->company_name,
                'location' => $job->location,
                'salary' => $job->salary,
                'type' => $job->type,
                'status' => $job->status,
                'applications_count' => $job->applications_count ?? 0,
                'hired_count' => $job->hired_count ?? 0,
                'shortlisted_count' => $job->shortlisted_count ?? 0,
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
                'per_page' => $perPage,
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
     * Export applications to Excel with optional filters
     */
    public function exportExcel(Request $request)
    {
        // Validate filter inputs
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,shortlisted,rejected,interview,hired',
            'score_min' => 'nullable|numeric|min:0|max:100',
            'score_max' => 'nullable|numeric|min:0|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'format' => 'nullable|in:excel,pdf',
        ]);

        $format = $validated['format'] ?? 'excel';
        $timestamp = Carbon::now()->format('d_m_Y_H_i_s');
        
        // Prepare filters array
        $filters = [
            'search' => $validated['search'] ?? null,
            'status' => $validated['status'] ?? null,
            'score_min' => $validated['score_min'] ?? null,
            'score_max' => $validated['score_max'] ?? null,
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
        ];

        // Export to Excel
        if ($format === 'excel' || $format === null) {
            $filename = "applicants_report_{$timestamp}.xlsx";
            return Excel::download(new ApplicationsExportWithFilters($filters), $filename);
        }
        
        // PDF export can be added later when library is installed
        return response()->json(['error' => 'PDF export not available yet'], 400);
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