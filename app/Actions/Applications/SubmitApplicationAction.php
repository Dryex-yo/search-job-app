<?php

namespace App\Actions\Applications;

use App\Models\Application;
use App\Traits\CalculatesProfileCompletion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SubmitApplicationAction
{
    use CalculatesProfileCompletion;
    public function execute(array $data): Application
    {
        $user = Auth::user();

        // Check if user profile is 100% complete
        $profileCompletion = $this->calculateProfileCompletion($user);
        
        if ($profileCompletion < 100) {
            throw ValidationException::withMessages([
                'profile' => "Profil Anda hanya {$profileCompletion}% lengkap. Silakan lengkapi profil Anda hingga 100% sebelum melamar pekerjaan.",
            ]);
        }

        // Determine resume path
        $resumePath = null;
        
        // If new resume is provided, save it
        if (isset($data['resume']) && $data['resume']) {
            $resumePath = $data['resume']->store('resumes', 'public');
        } else if ($user->resume_path) {
            // Otherwise use existing resume from profile
            $resumePath = $user->resume_path;
        } else {
            // No resume available
            throw ValidationException::withMessages([
                'resume' => 'CV diperlukan. Silakan upload CV di profile Anda atau pilih file CV saat melamar.',
            ]);
        }

        return Application::create([
            'job_id' => $data['job_id'],
            'user_id' => Auth::id(),
            'resume_path' => $resumePath,
            'cover_letter' => $data['cover_letter'] ?? null,
            'status' => 'pending',
        ]);
    }
}