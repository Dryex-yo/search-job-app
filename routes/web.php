<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecruiterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\RegisteredRecruiterController;

// --- Public Routes ---
Route::get('/', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{id}', [JobController::class, 'show'])->name('jobs.show');
Route::get('/applications/{id}', [JobController::class, 'trackApplication'])->name('applications.track');

// --- User Auth Routes ---
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
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

    Route::get('/settings', [AdminDashboard::class, 'settings'])->name('settings');

    // Recruiter registration routes - Admin only
    Route::get('/recruiters/create', [RegisteredRecruiterController::class, 'create'])->name('recruiters.create');
    Route::post('/recruiters', [RegisteredRecruiterController::class, 'store'])->name('recruiters.store');
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
        Route::patch('/applications/{id}', [AdminDashboard::class, 'update'])->name('applications.update');

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