<?php

namespace App\Actions\Applications;

use App\Models\Application;
use App\Traits\CalculatesProfileCompletion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SubmitApplicationAction
{
    use CalculatesProfileCompletion;
    public function execute(array $data): Application
    {
        $user = Auth::user();

        // Log the received data for debugging
        Log::info('SubmitApplicationAction - Received data', [
            'job_id' => $data['job_id'] ?? 'MISSING',
            'cover_letter_length' => strlen($data['cover_letter'] ?? ''),
            'cover_letter_preview' => substr($data['cover_letter'] ?? '', 0, 50),
            'resume_exists' => isset($data['resume']) && $data['resume'] ? true : false,
            'user_id' => Auth::id(),
        ]);

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

        // Ensure cover_letter is properly set (trim whitespace and validate)
        $coverLetter = isset($data['cover_letter']) ? trim($data['cover_letter']) : null;
        
        // Log before saving
        Log::info('SubmitApplicationAction - Before saving', [
            'cover_letter_length' => strlen($coverLetter ?? ''),
            'cover_letter_is_null' => $coverLetter === null,
            'cover_letter_is_empty' => $coverLetter === '',
        ]);

        $application = Application::create([
            'job_id' => $data['job_id'],
            'user_id' => Auth::id(),
            'resume_path' => $resumePath,
            'cover_letter' => $coverLetter,
            'status' => 'pending',
        ]);

        // Log after saving
        Log::info('SubmitApplicationAction - After saving', [
            'application_id' => $application->id,
            'saved_cover_letter_length' => strlen($application->cover_letter ?? ''),
            'saved_cover_letter' => substr($application->cover_letter ?? '', 0, 50),
        ]);

        return $application;
    }
}