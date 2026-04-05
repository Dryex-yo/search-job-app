<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // Check if user is admin
        /** @var User $user */
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            abort(403, 'Unauthorized to view audit logs');
        }

        // Get all activities with pagination
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        // Validate per_page to prevent abuse
        $perPage = in_array((int)$perPage, [15, 25, 50, 100]) ? (int)$perPage : 15;

        $activities = Activity::query()
            ->with(['subject', 'causer'])
            ->latest('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        // Transform activities to include readable information
        $logs = $activities->map(function (Activity $activity) {
            return $this->formatActivity($activity);
        });

        return Inertia::render('Admin/AuditLogs', [
            'logs' => $logs,
            'pagination' => [
                'current_page' => $activities->currentPage(),
                'total' => $activities->total(),
                'per_page' => $activities->perPage(),
                'last_page' => $activities->lastPage(),
            ]
        ]);
    }

    public function show(Activity $activity)
    {
        // Check if user is admin
        /** @var User $user */
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            abort(403, 'Unauthorized to view audit logs');
        }

        return response()->json([
            'log' => $this->formatActivityDetailed($activity)
        ]);
    }

    /**
     * Format activity for listing view
     */
    private function formatActivity(Activity $activity): array
    {
        $causer = $activity->causer;
        $subject = $activity->subject;

        return [
            'id' => $activity->id,
            'description' => $this->generateDescription($activity),
            'causer_name' => $causer->name ?? 'System',
            'causer_email' => $causer->email ?? 'N/A',
            'subject_type' => class_basename($activity->subject_type),
            'subject_id' => $activity->subject_id,
            'timestamp' => $activity->created_at->format('Y-m-d H:i:s'),
            'time_ago' => $activity->created_at->diffForHumans(),
            'changes' => $activity->properties['attributes'] ?? []
        ];
    }

    /**
     * Format activity with full details
     */
    private function formatActivityDetailed(Activity $activity): array
    {
        $causer = $activity->causer;
        $subject = $activity->subject;
        $format = $this->formatActivity($activity);

        $format['old_values'] = $activity->properties['old'] ?? [];
        $format['new_values'] = $activity->properties['attributes'] ?? [];
        $format['all_properties'] = $activity->properties;

        return $format;
    }

    /**
     * Generate human-readable description
     */
    private function generateDescription(Activity $activity): string
    {
        $causer = $activity->causer;
        $subject = $activity->subject;
        $causerName = $causer?->name ?? 'System';
        $subjectType = class_basename($activity->subject_type);

        // Get the changed attributes
        $changes = $activity->properties['attributes'] ?? [];
        $oldValues = $activity->properties['old'] ?? [];

        // Build a descriptive message
        if ($subjectType === 'Application') {
            if (isset($changes['status']) && isset($oldValues['status'])) {
                $applicantName = $subject?->user?->name ?? 'Unknown';
                $jobTitle = $subject?->job?->title ?? 'Unknown Position';
                return "{$causerName} changed {$applicantName}'s status from {$oldValues['status']} to {$changes['status']} for {$jobTitle}";
            }
            if (isset($changes['admin_notes'])) {
                $applicantName = $subject?->user?->name ?? 'Unknown';
                return "{$causerName} added/updated notes for {$applicantName}'s application";
            }
            if (isset($changes['ai_match_score'])) {
                $applicantName = $subject?->user?->name ?? 'Unknown';
                return "{$causerName} updated AI match score for {$applicantName}";
            }
        }

        // Default generic description
        return "{$causerName} {$activity->event} {$subjectType}";
    }
}
