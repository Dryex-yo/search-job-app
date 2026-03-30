<?php

namespace App\Http\Controllers;

use App\Actions\Jobs\ListAvailableJobsAction;
use App\Actions\Jobs\GetJobDetailsAction;
use App\Actions\Applications\SubmitApplicationAction;
use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    /**
     * Menampilkan halaman daftar lowongan kerja dengan Inertia.
     */
    public function index(Request $request, ListAvailableJobsAction $listJobsAction): Response
    {
        // Mengambil semua filter dari URL query string
        $filters = $request->only(['search', 'type', 'location', 'salary_min', 'salary_max']);
        
        // Jika hanya search diisi dan tidak ada filter lain, gunakan backward compatibility
        $filterArray = array_filter($filters); // Hapus nilai kosong
        
        // Mengambil jobs dengan filter
        $jobs = $listJobsAction->execute($filterArray);

        // Mengambil data unik untuk dropdown filters
        $jobTypes = Job::distinct()->pluck('type')->filter()->values();
        $locations = Job::distinct()->pluck('location')->filter()->values();

        return Inertia::render('Jobs/Index', [
            'jobs' => $jobs,
            'filters' => $filters,
            'jobTypes' => $jobTypes,
            'locations' => $locations,
        ]);
    }

    public function show(int $id, GetJobDetailsAction $getJobDetails)
    {
        $job = $getJobDetails->execute($id);

        // Kita kembalikan sebagai response JSON agar modal di Vue bisa mengambil datanya
        return response()->json($job);
    }

    public function apply(Request $request, SubmitApplicationAction $submitAction)
    {
        $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'resume' => 'required|mimes:pdf|max:2048', // Maksimal 2MB PDF
            'cover_letter' => 'nullable|string',
        ]);

        $submitAction->execute($request->all());

        return back()->with('message', 'Lamaran kamu berhasil dikirim!');
    }
}