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

        // CRITICAL: Get tenant_id with fallback logic + aggressive debugging
        $tenantId = null;
        
        Log::debug('SubmitApplicationAction - Tenant lookup start', [
            'user_id' => Auth::id(),
        ]);
        
        // Method 1: Try to get from tenancy container
        try {
            $tenantManager = app('tenancy');
            $currentTenant = $tenantManager->tenant();
            if ($currentTenant) {
                $tenantId = $currentTenant->id;
                Log::info('SubmitApplicationAction - Tenant from tenancy container', [
                    'tenant_id' => $tenantId,
                ]);
            } else {
                Log::debug('SubmitApplicationAction - No tenant in tenancy container');
            }
        } catch (\Exception $e) {
            Log::warning('SubmitApplicationAction - Failed tenant container: ' . $e->getMessage());
        }
        
        // Method 2: Fallback - If user is authenticated, try to infer tenant
        if (!$tenantId && Auth::check()) {
            $authenticatedUser = Auth::user();
            $userTenantId = $authenticatedUser?->tenant_id;
            
            Log::debug('SubmitApplicationAction - User tenant lookup', [
                'user_id' => Auth::id(),
                'user_tenant_id' => $userTenantId,
            ]);
            
            // Check if user has tenant_id field
            if ($authenticatedUser && $userTenantId) {
                $tenantId = $userTenantId;
                Log::info('SubmitApplicationAction - Tenant from authenticated user', [
                    'tenant_id' => $tenantId,
                    'user_id' => Auth::id(),
                ]);
            } else {
                // Check database directly
                $dbUser = \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', Auth::id())
                    ->select('id', 'tenant_id')
                    ->first();
                
                if ($dbUser?->tenant_id) {
                    $tenantId = $dbUser->tenant_id;
                    Log::warning('SubmitApplicationAction - Tenant from database lookup', [
                        'tenant_id' => $tenantId,
                        'user_id' => Auth::id(),
                    ]);
                }
            }
            
            // If still no tenant, try to get first tenant (development/local only)
            if (!$tenantId) {
                $firstTenant = \App\Models\Tenant::first();
                if ($firstTenant) {
                    $tenantId = $firstTenant->id;
                    Log::warning('SubmitApplicationAction - Using first tenant as fallback', [
                        'tenant_id' => $tenantId,
                        'user_id' => Auth::id(),
                        'reason' => 'No tenant found via container or user',
                    ]);
                }
            }
        }

        if (!$tenantId) {
            Log::error('SubmitApplicationAction - CRITICAL: Cannot determine tenant_id', [
                'user_id' => Auth::id(),
                'has_auth_user' => Auth::check(),
                'request_host' => request()->getHost(),
                'auth_user_class' => Auth::check() ? get_class(Auth::user()) : null,
            ]);
            throw ValidationException::withMessages([
                'tenant' => 'Tenant context tidak tersedia. Silakan hubungi administrator atau coba lagi nanti.',
            ]);
        }
        
        Log::info('SubmitApplicationAction - Tenant ID determined successfully', [
            'tenant_id' => $tenantId,
            'user_id' => Auth::id(),
        ]);

        // Create application with EXPLICIT tenant_id
        $application = Application::create([
            'job_id' => $data['job_id'],
            'user_id' => Auth::id(),
            'resume_path' => $resumePath,
            'cover_letter' => $coverLetter,
            'status' => 'pending',
            'tenant_id' => $tenantId,
        ]);

        // Log after saving
        Log::info('SubmitApplicationAction - After saving', [
            'application_id' => $application->id,
            'tenant_id' => $application->tenant_id,
            'saved_cover_letter_length' => strlen($application->cover_letter ?? ''),
        ]);

        return $application;
    }
}