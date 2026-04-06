<?php

namespace App\Http\Controllers;

use App\Actions\Jobs\ListAvailableJobsAction;
use App\Actions\Jobs\GetJobDetailsAction;
use App\Actions\Applications\SubmitApplicationAction;
use App\Models\Job;
use App\Models\Application;
use App\Traits\CalculatesProfileCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    use CalculatesProfileCompletion;
    /**
     * Menampilkan halaman daftar lowongan kerja dengan Inertia.
     */
    public function index(Request $request, ListAvailableJobsAction $listJobsAction): Response
    {
        // Validate and sanitize filter inputs for security
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,closed',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
        ]);

        // Mengambil semua filter dari URL query string
        $filters = array_filter($validated); // Hapus nilai kosong
        
        // Mengambil jobs dengan filter
        $jobs = $listJobsAction->execute($filters);

        // Mengambil data unik untuk dropdown filters
        $jobTypes = Job::distinct()->pluck('type')->filter()->values();
        $locations = Job::distinct()->pluck('location')->filter()->values();

        return Inertia::render('Jobs/Index', [
            'jobs' => $jobs,
            'filters' => $validated,
            'jobTypes' => $jobTypes,
            'locations' => $locations,
        ]);
    }

    public function show(int $id, GetJobDetailsAction $getJobDetails)
    {
        $job = $getJobDetails->execute($id);
        
        // Get profile completion if user is authenticated
        $profileCompletion = 0;
        $user = Auth::user();
        if ($user) {
            $profileCompletion = $this->calculateProfileCompletion($user);
        }

        // Set browser cache for 5 minutes (job details don't change frequently)
        // This reduces database queries on page refreshes
        return Inertia::render('Jobs/Show', [
            'job' => $job,
            'profileCompletion' => $profileCompletion,
        ])->withViewData(['cacheControl' => 'public, max-age=300']);
    }

    public function apply(Request $request, SubmitApplicationAction $submitAction)
    {
        $request->validate([
            'job_id' => 'required|integer|exists:jobs,id',
            'resume' => 'nullable|mimes:pdf|max:2048', // Maksimal 2MB PDF, optional jika ada di profile
            'cover_letter' => 'required|string|min:10|max:5000',
        ]);

        $submitAction->execute($request->all());

        return back()->with('message', 'Lamaran kamu berhasil dikirim!');
    }

    public function trackApplication(int $id)
    {
        $application = Application::with(['user', 'job'])
            ->findOrFail($id);

        return Inertia::render('Applications/Track', [
            'application' => [
                'id' => $application->id,
                'status' => $application->status,
                'ai_match_score' => $application->ai_match_score,
                'ai_analysis_status' => $application->ai_analysis_status,
                'ai_analysis_details' => $application->ai_analysis_details,
                'ai_analyzed_at' => $application->ai_analyzed_at,
                'job_title' => $application->job->title,
                'company_name' => $application->job->company_name,
                'location' => $application->job->location,
                'created_at' => $application->created_at,
                'updated_at' => $application->updated_at,
            ]
        ]);
    }
}