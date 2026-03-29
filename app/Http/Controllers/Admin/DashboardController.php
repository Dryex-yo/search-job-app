<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Actions\Applications\GetApplicationsAction;
use Inertia\Inertia;

use App\Actions\Applications\UpdateApplicationStatusAction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $applicants = Application::with(['user', 'job'])
            ->latest()
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'name' => $app->user->name,
                    'role' => $app->job->title, // Mengambil judul kerjaan
                    'status' => ucfirst($app->status), // Biar huruf depannya kapital (Pending)
                    'date' => $app->created_at->format('d M Y'),
                    'avatar' => strtoupper(substr($app->user->name, 0, 2)),
                    'resume' => $app->resume_path,
                ];
            });

        return Inertia::render('Admin/Applicants', [
            'applicants' => $applicants
        ]);
    }

    public function update(Request $request, $id, UpdateApplicationStatusAction $updateStatus)
    {
        $request->validate([
            'status' => 'required|in:pending,shortlisted,rejected'
        ]);

        $updateStatus->execute($id, $request->status);

        return back()->with('message', 'Status pelamar berhasil diperbarui!');
    }
}