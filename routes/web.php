<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;

// --- Public Routes ---
Route::get('/', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{id}', [JobController::class, 'show'])->name('jobs.show');

// --- User Auth Routes ---
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/jobs/apply', [JobController::class, 'apply'])->name('jobs.apply');
});

// --- Admin Routes (Fixing the Black Screen) ---
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Ini akan jadi: admin.dashboard (URL: /admin/dashboard)
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    
    // Ini akan jadi: admin.applicants (URL: /admin/applicants)
    Route::get('/applicants', function () {
        return Inertia::render('Admin/Applicants');
    })->name('applicants');
        
    Route::get('/analytics', function () {
        return Inertia::render('Admin/Dashboard'); // Arahkan ke Dashboard dulu jika file belum ada
    })->name('analytics');

    Route::get('/jobs', function () {
        return Inertia::render('Admin/Dashboard'); // Arahkan ke Dashboard dulu jika file belum ada
    })->name('jobs');

    Route::get('/settings', function () {
        return Inertia::render('Admin/Dashboard'); // Arahkan ke Dashboard dulu jika file belum ada
    })->name('settings');

    Route::patch('/applications/{id}', [AdminDashboard::class, 'update'])->name('applications.update');
});

require __DIR__.'/auth.php';