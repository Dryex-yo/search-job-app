<?php

namespace App\Http\Controllers;

use App\Actions\Jobs\ListAvailableJobsAction;
use App\Actions\Jobs\GetJobDetailsAction;
use App\Actions\Applications\SubmitApplicationAction;
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
        // Mengambil input 'search' dari URL query string
        $jobs = $listJobsAction->execute($request->input('search'));

        return Inertia::render('Jobs/Index', [
            'jobs' => $jobs,
            'filters' => $request->only(['search']) // Kirim balik input ke Vue
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