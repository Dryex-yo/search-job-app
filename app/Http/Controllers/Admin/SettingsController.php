<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Show the settings page
     */
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!($user->hasRole('admin') || $user->can('change-settings'))) {
            abort(403, 'Unauthorized to access settings');
        }

        $settings = Settings::getInstance();

        // Get analytics data for dashboard context
        $analytics = app(\App\Http\Controllers\Admin\DashboardController::class)->getAnalyticsDataPublic();

        return Inertia::render('Admin/Settings', [
            'settings' => [
                'platform_name' => $settings->platform_name,
                'support_email' => $settings->support_email,
                'max_file_upload_mb' => $settings->max_file_upload_mb,
                'email_new_applications' => $settings->email_new_applications,
                'email_job_expiry' => $settings->email_job_expiry,
                'email_weekly_reports' => $settings->email_weekly_reports,
                'email_user_feedback' => $settings->email_user_feedback,
                'two_factor_enabled' => $settings->two_factor_enabled,
                'hiring_fee_per_person' => $settings->hiring_fee_per_person,
            ],
            'analytics' => $analytics,
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!($user->hasRole('admin') || $user->can('change-settings'))) {
            abort(403, 'Unauthorized to update settings');
        }

        $validated = $request->validate([
            'platform_name' => 'required|string|max:255',
            'support_email' => 'required|email|max:255',
            'max_file_upload_mb' => 'required|integer|min:1|max:1000',
            'email_new_applications' => 'boolean',
            'email_job_expiry' => 'boolean',
            'email_weekly_reports' => 'boolean',
            'email_user_feedback' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'hiring_fee_per_person' => 'required|numeric|min:0|max:99999.99',
        ]);

        $settings = Settings::getInstance();
        $settings->update($validated);

        return back()->with('success', 'Pengaturan berhasil disimpan!');
    }
}
