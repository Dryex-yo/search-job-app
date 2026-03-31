<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use App\Actions\Applications\UpdateApplicationStatusAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard', [
            'analytics' => $this->getAnalyticsData()
        ]);
    }

    public function index()
    {
        $applicants = Application::with(['user', 'job'])
            ->latest()
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'name' => $app->user->name ?? 'Unknown User',
                    'role' => $app->job->title ?? 'Unknown Role', 
                    'status' => ucfirst($app->status), 
                    'date' => $app->created_at->format('d M Y'),
                    'avatar' => strtoupper(substr($app->user->name ?? '??', 0, 2)),
                    'resume_path' => $app->resume_path,
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
            'notes' => 'nullable|string|max:5000'
        ]);

        // Update using the action for status
        $updateStatus->execute($id, $request->status);

        // Also update notes if provided
        if ($request->has('notes')) {
            Application::findOrFail($id)->update(['notes' => $request->notes]);
        }

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
            // Gunakan response() download agar Intelephense lebih tenang
            return response()->download(storage_path('app/public/' . $application->resume_path));
        }

        return back()->with('error', 'Maaf, file resume tidak ditemukan di server.');
    }

    /**
     * Get analytics data for admin pages
     */
    private function getAnalyticsData()
    {
        return [
            'total_jobs' => Job::count(),
            'total_applications' => Application::count(),
            'total_users' => User::whereNotIn('id', [Auth::id()])->count(),
            'active_jobs' => Job::where('status', 'active')->count(),
            'pending_applications' => Application::where('status', 'pending')->count(),
            'shortlisted_applications' => Application::where('status', 'shortlisted')->count(),
            'rejected_applications' => Application::where('status', 'rejected')->count(),
            'hired_count' => Application::where('status', 'hired')->count(),
        ];
    }

    public function analytics()
    {
        return Inertia::render('Admin/Analytics', [
            'analytics' => $this->getAnalyticsData(),
            'chartData' => [
                'weeklyApplicants' => $this->getWeeklyApplicantsData(),
                'statusDistribution' => $this->getStatusDistribution(),
            ]
        ]);
    }

    public function jobs()
    {
        return Inertia::render('Admin/Jobs', [
            'analytics' => $this->getAnalyticsData()
        ]);
    }

    public function settings()
    {
        return Inertia::render('Admin/Settings', [
            'analytics' => $this->getAnalyticsData()
        ]);
    }

    /**
     * Get weekly applicants data for line chart (last 12 weeks)
     */
    private function getWeeklyApplicantsData()
    {
        $weeksData = [];
        $categories = [];
        
        // Get data for last 12 weeks
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
    }

    /**
     * Get status distribution for pie chart (Hired vs Rejected)
     */
    private function getStatusDistribution()
    {
        $hiredCount = Application::where('status', 'hired')->count();
        $rejectedCount = Application::where('status', 'rejected')->count();
        $otherCount = Application::whereNotIn('status', ['hired', 'rejected'])->count();
        
        return [
            'series' => [$hiredCount, $rejectedCount, $otherCount],
            'labels' => ['Hired 🎉', 'Rejected ❌', 'In Progress ⏳'],
            'colors' => ['#10b981', '#ef4444', '#f59e0b'],
        ];
    }
}