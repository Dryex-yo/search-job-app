<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecruiterController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantRegistrationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\RegisteredRecruiterController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\InterviewSchedulingController;
use App\Http\Controllers\Admin\SettingsController;

// --- Public Routes ---
Route::get('/', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{id}', [JobController::class, 'show'])->name('jobs.show');
Route::get('/applications/{id}', [JobController::class, 'trackApplication'])->name('applications.track');

// --- Tenant Registration Routes ---
Route::get('/tenant/register', [TenantRegistrationController::class, 'showRegistrationForm'])->name('tenant.register.form');
Route::post('/tenant/register', [TenantRegistrationController::class, 'register'])->name('tenant.register');

// --- User Auth Routes ---
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/upload-photo', [ProfileController::class, 'uploadProfilePhoto'])->name('profile.upload-photo');
    Route::post('/profile/upload-resume', [ProfileController::class, 'uploadResume'])->name('profile.upload-resume');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/jobs/apply', [JobController::class, 'apply'])->name('jobs.apply');
});

// --- Admin Routes (Fixing the Black Screen) ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Ini akan jadi: admin.dashboard (URL: /admin/dashboard)
    Route::get('/dashboard', [AdminDashboard::class, 'dashboard'])->name('dashboard');

    // Job management routes - Admin only
    Route::get('/jobs', [AdminDashboard::class, 'jobs'])->name('jobs');
    Route::post('/jobs', [AdminDashboard::class, 'storeJob'])->name('jobs.store');
    Route::patch('/jobs/{id}', [AdminDashboard::class, 'updateJob'])->name('jobs.update');
    Route::delete('/jobs/{id}', [AdminDashboard::class, 'deleteJob'])->name('jobs.destroy');

    // User management routes - Admin only
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/password', [UserManagementController::class, 'updatePassword'])->name('users.update-password');

    Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Audit logs routes - Admin only
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{activity}', [AuditLogController::class, 'show'])->name('audit-logs.show');

    // Recruiter registration routes - Admin only
    Route::get('/recruiters/create', [RegisteredRecruiterController::class, 'create'])->name('recruiters.create');
    Route::post('/recruiters', [RegisteredRecruiterController::class, 'store'])->name('recruiters.store');

    // Tenant management routes - Superadmin only
    Route::middleware('role:superadmin')->group(function () {
        Route::resource('/tenants', TenantController::class);
        Route::patch('/tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::patch('/tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
    });
});

// Recruiter and Admin routes - Applicant management
// Updated to allow both admin and recruiter
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('recruiter')->group(function () {
        // Ini akan jadi: admin.applicants (URL: /admin/applicants)
        Route::get('/applicants', [AdminDashboard::class, 'index'])->name('applicants');
        Route::get('/applicants/{id}', [AdminDashboard::class, 'show'])->name('applicants.show');
        Route::get('/applicants/download/{id}', [AdminDashboard::class, 'downloadResume'])->name('applicants.download');
        Route::get('/applicants/export/excel', [AdminDashboard::class, 'exportExcel'])->name('applicants.export.excel');
        // Bulk update must come BEFORE {id} route to match it first
        Route::patch('/applications/bulk-update', [AdminDashboard::class, 'bulkUpdate'])->name('applications.bulk-update');
        Route::patch('/applications/{id}', [AdminDashboard::class, 'update'])->name('applications.update');

        // Interview scheduling routes
        Route::post('/applications/{id}/schedule-interview', [InterviewSchedulingController::class, 'schedule'])->name('applications.schedule-interview');
        Route::patch('/applications/{id}/reschedule-interview', [InterviewSchedulingController::class, 'reschedule'])->name('applications.reschedule-interview');
        Route::delete('/applications/{id}/cancel-interview', [InterviewSchedulingController::class, 'cancel'])->name('applications.cancel-interview');
        Route::get('/applications/{id}/interview-details', [InterviewSchedulingController::class, 'getDetails'])->name('applications.interview-details');

        Route::get('/analytics', [AdminDashboard::class, 'analytics'])->name('analytics');
    });
});

// --- Recruiter Routes (Dedicated Recruiter Portal) ---
Route::middleware(['auth', 'recruiter'])->prefix('recruiter')->name('recruiter.')->group(function () {
    Route::get('/dashboard', [RecruiterController::class, 'dashboard'])->name('dashboard');
    Route::get('/applicants', [RecruiterController::class, 'applicants'])->name('applicants');
    Route::get('/applicants/{id}', [RecruiterController::class, 'showApplicant'])->name('applicants.show');
    Route::patch('/applicants/{id}', [RecruiterController::class, 'updateApplicant'])->name('applicants.update');
    Route::get('/applicants/{id}/download', [RecruiterController::class, 'downloadResume'])->name('applicants.download');
    Route::get('/applicants/export/excel', [RecruiterController::class, 'exportApplicants'])->name('applicants.export');
    Route::get('/analytics', [RecruiterController::class, 'analytics'])->name('analytics');
});

require __DIR__.'/auth.php';