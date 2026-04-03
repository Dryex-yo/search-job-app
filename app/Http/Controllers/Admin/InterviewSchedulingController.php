<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Actions\Applications\ScheduleApplicationInterviewAction;
use App\Services\InterviewSchedulingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class InterviewSchedulingController extends Controller
{
    protected ScheduleApplicationInterviewAction $scheduleAction;
    protected InterviewSchedulingService $interviewService;

    public function __construct(
        ScheduleApplicationInterviewAction $scheduleAction,
        InterviewSchedulingService $interviewService
    ) {
        $this->scheduleAction = $scheduleAction;
        $this->interviewService = $interviewService;
    }

    /**
     * Schedule an interview for an application
     * 
     * POST /admin/applications/{id}/schedule-interview
     */
    public function schedule(Request $request, int $id)
    {
        try {
            $application = Application::findOrFail($id);

            // Verify authorization
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if (!$user->hasRole('admin')) {
                abort(403, 'Unauthorized to schedule interviews');
            }

            // Validate request
            $validated = $request->validate([
                'scheduled_at' => 'required|date|after:now',
                'duration_minutes' => 'required|integer|min:15|max:480',
                'interview_type' => 'required|in:technical,hr,general,final',
                'meeting_provider' => 'required|in:zoom,google_meet,auto',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Schedule the interview
            $result = $this->scheduleAction->execute($application, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Interview scheduled successfully',
                'data' => [
                    'interview_scheduled_at' => $application->interview_scheduled_at,
                    'interview_meeting_link' => $application->interview_meeting_link,
                    'interview_meeting_provider' => $application->interview_meeting_provider,
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule interview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reschedule an interview
     * 
     * PATCH /admin/applications/{id}/reschedule-interview
     */
    public function reschedule(Request $request, int $id)
    {
        try {
            $application = Application::findOrFail($id);

            // Verify authorization
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if (!$user->hasRole('admin')) {
                abort(403, 'Unauthorized to reschedule interviews');
            }

            if (!$application->interview_scheduled_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'No interview scheduled for this application'
                ], 404);
            }

            // Validate request
            $validated = $request->validate([
                'scheduled_at' => 'required|date|after:now',
                'duration_minutes' => 'nullable|integer|min:15|max:480',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Add existing duration if not provided
            if (!isset($validated['duration_minutes'])) {
                $validated['duration_minutes'] = $application->interview_duration_minutes ?? 60;
            }

            // Reschedule the interview
            $result = $this->interviewService->rescheduleInterview($application, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Interview rescheduled successfully',
                'data' => $result
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reschedule interview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel an interview
     * 
     * DELETE /admin/applications/{id}/cancel-interview
     */
    public function cancel(Request $request, int $id)
    {
        try {
            $application = Application::findOrFail($id);

            // Verify authorization
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if (!$user->hasRole('admin')) {
                abort(403, 'Unauthorized to cancel interviews');
            }

            if (!$application->interview_scheduled_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'No interview scheduled for this application'
                ], 404);
            }

            // Get cancellation reason if provided
            $reason = $request->input('reason', '');

            // Cancel the interview
            $this->interviewService->cancelInterview($application, $reason);

            return response()->json([
                'success' => true,
                'message' => 'Interview cancelled successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel interview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get interview details for an application
     * 
     * GET /admin/applications/{id}/interview-details
     */
    public function getDetails(int $id)
    {
        try {
            $application = Application::findOrFail($id);

            /** @var \App\Models\User $user */
            $user = Auth::user();
            if (!$user->hasRole('admin')) {
                abort(403, 'Unauthorized');
            }

            $details = $this->interviewService->getInterviewDetails($application);

            if (!$details) {
                return response()->json([
                    'success' => false,
                    'message' => 'No interview scheduled for this application'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $details
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch interview details: ' . $e->getMessage()
            ], 500);
        }
    }
}
