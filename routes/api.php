<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApplicantsController;

/**
 * @apiVersion 1.0
 * 
 * Prefix: /api/v1
 * Authentication: Sanctum Bearer Token
 */

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    /**
     * Get list of applicants
     * 
     * Returns a paginated list of all applicants with their details,
     * application status, and related information.
     * 
     * @authenticated
     */
    Route::get('/applicants', [ApplicantsController::class, 'index'])->name('api.applicants.index');

    /**
     * Get single applicant details
     * 
     * Returns detailed information about a specific applicant
     * including their profile, CV, and application history.
     * 
     * @authenticated
     */
    Route::get('/applicants/{id}', [ApplicantsController::class, 'show'])->name('api.applicants.show');

    /**
     * Update applicant details
     * 
     * Update the status or other details of an applicant's application.
     * 
     * @authenticated
     */
    Route::patch('/applicants/{id}', [ApplicantsController::class, 'update'])->name('api.applicants.update');

    /**
     * Delete applicant
     * 
     * Remove an applicant from the system.
     * 
     * @authenticated
     */
    Route::delete('/applicants/{id}', [ApplicantsController::class, 'destroy'])->name('api.applicants.destroy');
});
