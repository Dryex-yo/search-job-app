<?php

namespace App\Actions\Applications;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class SubmitApplicationAction
{
    public function execute(array $data): Application
    {
        // Logika simpan file CV ke folder 'resumes' di storage/app/public
        $path = $data['resume']->store('resumes', 'public');

        return Application::create([
            'job_id' => $data['job_id'],
            'user_id' => Auth::id(),
            'resume_path' => $path,
            'cover_letter' => $data['cover_letter'] ?? null,
            'status' => 'pending',
        ]);
    }
}