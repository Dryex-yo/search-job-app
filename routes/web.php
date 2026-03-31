<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;

// --- Public Routes ---
Route::get('/', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{id}', [JobController::class, 'show'])->name('jobs.show');

// --- User Auth Routes ---
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/jobs/apply', [JobController::class, 'apply'])->name('jobs.apply');
});

// --- Admin Routes (Fixing the Black Screen) ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Ini akan jadi: admin.dashboard (URL: /admin/dashboard)
    Route::get('/dashboard', [AdminDashboard::class, 'dashboard'])->name('dashboard');

    // Ini akan jadi: admin.applicants (URL: /admin/applicants)
    Route::get('/applicants', [AdminDashboard::class, 'index'])->name('applicants');
    Route::get('/applicants/{id}', [AdminDashboard::class, 'show'])->name('applicants.show');
    Route::get('/applicants/download/{id}', [AdminDashboard::class, 'downloadResume'])->name('applicants.download');
    Route::patch('/applications/{id}', [AdminDashboard::class, 'update'])->name('applications.update');
        
    Route::get('/analytics', [AdminDashboard::class, 'analytics'])->name('analytics');

    Route::get('/jobs', [AdminDashboard::class, 'jobs'])->name('jobs');

    Route::get('/settings', [AdminDashboard::class, 'settings'])->name('settings');
});

require __DIR__.'/auth.php';