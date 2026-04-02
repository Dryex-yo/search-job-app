<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApplicationsExport;

class RecruiterController extends Controller
{
    /**
     * Show recruiter dashboard
     */
    public function dashboard()
    {
        $recruiter = Auth::user();
        
        return Inertia::render('Recruiter/Dashboard', [
            'user' => $recruiter,
            'analytics' => $this->getRecruiterAnalytics()
        ]);
    }

    /**
     * Show applicants list for recruiter
     */
    public function applicants()
    {
        // Get all applications with their details
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
                    'cover_letter' => $app->cover_letter ?? null,
                ];
            });

        return Inertia::render('Recruiter/Applicants', [
            'applicants' => $applicants,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Show detailed view of a single application
     */
    public function showApplicant($id)
    {
        $application = Application::with(['user', 'job'])->findOrFail($id);

        return Inertia::render('Recruiter/ApplicantDetail', [
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
            ],
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update application status
     */
    public function updateApplicant(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,shortlisted,rejected,interview,hired',
            'notes' => 'nullable|string|max:5000'
        ]);

        $application = Application::findOrFail($id);
        $application->update([
            'status' => $request->status,
            'notes' => $request->notes ?? $application->notes
        ]);

        return back()->with('message', 'Status pelamar berhasil diperbarui!');
    }

    /**
     * Download resume for applicant
     */
    public function downloadResume($id)
    {
        $application = Application::findOrFail($id);
        
        if ($application->resume_path && Storage::disk('public')->exists($application->resume_path)) {
            return response()->download(storage_path('app/public/' . $application->resume_path));
        }

        return back()->with('error', 'Maaf, file resume tidak ditemukan di server.');
    }

    /**
     * Export applicants to Excel
     */
    public function exportApplicants()
    {
        return Excel::download(new ApplicationsExport, 'applicants.xlsx');
    }

    /**
     * Show recruiter analytics
     */
    public function analytics()
    {
        return Inertia::render('Recruiter/Analytics', [
            'analytics' => $this->getRecruiterAnalytics(),
            'chartData' => [
                'weeklyApplicants' => $this->getWeeklyApplicantsData(),
                'statusDistribution' => $this->getStatusDistribution(),
                'monthlyApplications' => $this->getMonthlyApplicationsChart(),
            ],
            'topPerformingJobs' => $this->getTopPerformingJobs(),
            'user' => Auth::user(),
        ]);
    }

    /**
     * Get analytics data for recruiter dashboard
     */
    private function getRecruiterAnalytics()
    {
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
            'total_candidates' => Application::distinct('user_id')->count('user_id'),
            'pending_applications' => $pendingCount,
            'shortlisted_applications' => $shortlistedCount,
            'rejected_applications' => $rejectedCount,
            'hired_count' => $hiredCount,
            'success_rate' => $successRate,
            'total_revenue' => $totalRevenue,
            'monthly_applications' => $monthlyData,
        ];
    }

    /**
     * Get weekly applicants data
     */
    private function getWeeklyApplicantsData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Application::whereDate('created_at', $date->toDateString())->count();
            $data[] = [
                'day' => $date->format('D'),
                'count' => $count
            ];
        }
        return $data;
    }

    /**
     * Get status distribution
     */
    private function getStatusDistribution()
    {
        return [
            'pending' => Application::where('status', 'pending')->count(),
            'shortlisted' => Application::where('status', 'shortlisted')->count(),
            'rejected' => Application::where('status', 'rejected')->count(),
            'interview' => Application::where('status', 'interview')->count(),
            'hired' => Application::where('status', 'hired')->count(),
        ];
    }

    /**
     * Get monthly applications chart data
     */
    private function getMonthlyApplicationsChart()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Application::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $data[] = [
                'month' => $date->format('M'),
                'count' => $count
            ];
        }
        return $data;
    }

    /**
     * Get top performing jobs
     */
    private function getTopPerformingJobs()
    {
        return Job::withCount('applications')
            ->orderBy('applications_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'applications_count' => $job->applications_count,
                    'hired_count' => Application::where('job_id', $job->id)->where('status', 'hired')->count(),
                ];
            });
    }

    /**
     * Get monthly applications data
     */
    private function getMonthlyApplicationsData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Application::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $data[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }
        return $data;
    }
}
