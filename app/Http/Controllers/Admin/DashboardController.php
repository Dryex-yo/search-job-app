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
            'status' => 'required|in:pending,shortlisted,rejected,interview,hired'
        ]);

        $updateStatus->execute($id, $request->status);

        return back()->with('message', 'Status pelamar berhasil diperbarui!');
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
            'analytics' => $this->getAnalyticsData()
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
}