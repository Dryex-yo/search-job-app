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

        // Check if user profile is 100% complete
        $profileCompletion = $this->calculateProfileCompletion($user);
        
        if ($profileCompletion < 100) {
            throw ValidationException::withMessages([
                'profile' => "Profil Anda hanya {$profileCompletion}% lengkap. Silakan lengkapi profil Anda hingga 100% sebelum melamar pekerjaan.",
            ]);
        }

        // Determine resume path - optimized path resolution
        $resumePath = null;
        
        if (isset($data['resume']) && $data['resume']) {
            // If new resume is provided, save it (this is the only I/O operation during submission)
            $resumePath = $data['resume']->store('resumes', 'public');
        } elseif ($user->resume_path) {
            // Use existing resume from profile
            $resumePath = $user->resume_path;
        } else {
            throw ValidationException::withMessages([
                'resume' => 'CV diperlukan. Silakan upload CV di profile Anda atau pilih file CV saat melamar.',
            ]);
        }

        // Trim cover letter - simple string operation
        $coverLetter = isset($data['cover_letter']) ? trim($data['cover_letter']) : null;

        // Get tenant_id efficiently - single attempt with fallback
        $tenantId = $this->getTenantId($user);
        
        if (!$tenantId) {
            Log::error('SubmitApplicationAction - Cannot determine tenant_id', [
                'user_id' => Auth::id(),
            ]);
            throw ValidationException::withMessages([
                'tenant' => 'Tenant context tidak tersedia. Silakan hubungi administrator atau coba lagi nanti.',
            ]);
        }

        // Create application - single database write operation
        $application = Application::create([
            'job_id' => $data['job_id'],
            'user_id' => Auth::id(),
            'resume_path' => $resumePath,
            'cover_letter' => $coverLetter,
            'status' => 'pending',
            'tenant_id' => $tenantId,
        ]);

        return $application;
    }

    /**
     * Get tenant ID efficiently with minimal lookup overhead
     */
    private function getTenantId($user): ?int
    {
        // First try: Get from tenancy container (fastest)
        try {
            $tenantManager = app('tenancy');
            $currentTenant = $tenantManager->tenant();
            if ($currentTenant) {
                return $currentTenant->id;
            }
        } catch (\Exception $e) {
            // Tenancy container not available, try fallback
        }

        // Second try: Get from authenticated user object (cached in memory)
        if ($user?->tenant_id) {
            return $user->tenant_id;
        }

        // Final fallback: Query first tenant (for development/simple setups)
        return \App\Models\Tenant::query()->value('id');
    }
}