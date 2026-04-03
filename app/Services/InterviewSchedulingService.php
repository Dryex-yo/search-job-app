<?php

namespace App\Services;

use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InterviewSchedulingService
{
    protected GoogleCalendarService $calendarService;
    protected ZoomGoogleMeetService $meetingService;

    public function __construct(
        GoogleCalendarService $calendarService,
        ZoomGoogleMeetService $meetingService
    ) {
        $this->calendarService = $calendarService;
        $this->meetingService = $meetingService;
    }

    /**
     * Schedule an interview for an application
     * 
     * @param Application $application
     * @param array $scheduleData [
     *     'scheduled_at' => DateTime (interview date/time),
     *     'duration_minutes' => int,
     *     'meeting_provider' => 'zoom' | 'google_meet' | 'auto',
     *     'interview_type' => 'technical' | 'hr' | 'general',
     *     'notes' => string (optional)
     * ]
     * @return array Interview details
     */
    public function scheduleInterview(Application $application, array $scheduleData): array
    {
        try {
            // Prepare interview data
            $admin = $application->admin;
            $applicant = $application->user;
            $job = $application->job;

            if (!$admin || !$applicant || !$job) {
                throw new \Exception('Missing required relationships for interview scheduling');
            }

            // Validate required fields
            if (!isset($scheduleData['scheduled_at'])) {
                throw new \Exception('Interview datetime required');
            }

            $scheduledAt = $scheduleData['scheduled_at'] instanceof Carbon 
                ? $scheduleData['scheduled_at']
                : Carbon::parse($scheduleData['scheduled_at']);

            $durationMinutes = $scheduleData['duration_minutes'] ?? 60;
            $meetingProvider = $scheduleData['meeting_provider'] ?? 'auto';
            $interviewType = $scheduleData['interview_type'] ?? 'general';

            // Generate meeting link
            $meetingLink = $this->generateMeetingLink(
                $job->title,
                $scheduledAt,
                $durationMinutes,
                $meetingProvider
            );

            // Create calendar event
            $calendarEventId = $this->createCalendarEvent(
                $admin->email,
                $applicant->email,
                $applicant->name,
                $job->title,
                $scheduledAt,
                $durationMinutes,
                $meetingLink,
                $scheduleData
            );

            // Update application with interview details
            $application->update([
                'interview_scheduled_at' => $scheduledAt,
                'interview_duration_minutes' => $durationMinutes,
                'interview_type' => $interviewType,
                'interview_meeting_link' => $meetingLink,
                'interview_calendar_event_id' => $calendarEventId,
                'interview_meeting_provider' => $meetingProvider,
                'interview_notes' => $scheduleData['notes'] ?? null,
            ]);

            Log::info('Interview scheduled successfully', [
                'application_id' => $application->id,
                'applicant' => $applicant->name,
                'scheduled_at' => $scheduledAt,
                'meeting_link' => $meetingLink
            ]);

            return [
                'success' => true,
                'meeting_link' => $meetingLink,
                'calendar_event_id' => $calendarEventId,
                'scheduled_at' => $scheduledAt->toIso8601String(),
                'provider' => $meetingProvider,
                'meeting_type' => $interviewType
            ];
        } catch (\Exception $e) {
            Log::error('Interview scheduling failed: ' . $e->getMessage(), [
                'application_id' => $application->id,
                'error' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Reschedule an existing interview
     */
    public function rescheduleInterview(Application $application, array $newScheduleData): array
    {
        try {
            if (!$application->interview_calendar_event_id) {
                throw new \Exception('Interview not found for this application');
            }

            $scheduledAt = Carbon::parse($newScheduleData['scheduled_at']);
            $durationMinutes = $newScheduleData['duration_minutes'] ?? 60;
            $newMeetingLink = $newScheduleData['meeting_link'] ?? $application->interview_meeting_link;

            // Update calendar event
            $this->calendarService->updateInterviewEvent(
                $application->interview_calendar_event_id,
                $application->admin->email,
                $scheduledAt,
                $scheduledAt->copy()->addMinutes($durationMinutes),
                $newMeetingLink
            );

            // Update application
            $application->update([
                'interview_scheduled_at' => $scheduledAt,
                'interview_duration_minutes' => $durationMinutes,
                'interview_meeting_link' => $newMeetingLink,
                'interview_notes' => $newScheduleData['notes'] ?? $application->interview_notes,
            ]);

            Log::info('Interview rescheduled successfully', [
                'application_id' => $application->id,
                'new_scheduled_at' => $scheduledAt
            ]);

            return [
                'success' => true,
                'message' => 'Interview rescheduled successfully',
                'scheduled_at' => $scheduledAt->toIso8601String()
            ];
        } catch (\Exception $e) {
            Log::error('Interview rescheduling failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel an interview
     */
    public function cancelInterview(Application $application, string $reason = ''): bool
    {
        try {
            if ($application->interview_calendar_event_id) {
                $this->calendarService->deleteInterviewEvent(
                    $application->interview_calendar_event_id
                );
            }

            // Delete Zoom meeting if exists
            if ($application->interview_meeting_provider === 'zoom' && 
                preg_match('/\/j\/(\d+)/', $application->interview_meeting_link, $matches)) {
                try {
                    $this->meetingService->deleteZoomMeeting($matches[1]);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete Zoom meeting: ' . $e->getMessage());
                    // Continue with cancellation even if Zoom deletion fails
                }
            }

            // Clear interview fields
            $application->update([
                'interview_scheduled_at' => null,
                'interview_duration_minutes' => null,
                'interview_type' => null,
                'interview_meeting_link' => null,
                'interview_calendar_event_id' => null,
                'interview_meeting_provider' => null,
                'interview_notes' => $reason ? "Cancelled: {$reason}" : 'Cancelled',
                'interview_cancelled_at' => now(),
            ]);

            Log::info('Interview cancelled', [
                'application_id' => $application->id,
                'reason' => $reason
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Interview cancellation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate meeting link based on provider
     */
    private function generateMeetingLink(
        string $topic,
        Carbon $startTime,
        int $durationMinutes,
        string $provider
    ): string {
        try {
            if ($provider === 'auto') {
                // Auto-detect based on configuration
                $provider = env('DEFAULT_MEETING_PROVIDER', 'google_meet');
            }

            if ($provider === 'zoom' && env('ZOOM_CLIENT_ID')) {
                $result = $this->meetingService->createZoomMeeting($topic, $startTime, $durationMinutes);
                return $result['link'];
            } elseif ($provider === 'google_meet') {
                $result = $this->meetingService->createGoogleMeeting(
                    $topic,
                    $startTime,
                    $startTime->copy()->addMinutes($durationMinutes)
                );
                return $result['link'];
            } else {
                // Fallback to simple link
                $result = $this->meetingService->generateSimpleMeetingLink('generic');
                return $result['link'];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create preferred meeting link, using fallback: ' . $e->getMessage());
            $result = $this->meetingService->generateSimpleMeetingLink();
            return $result['link'];
        }
    }

    /**
     * Create calendar event for both admin and applicant
     */
    private function createCalendarEvent(
        string $adminEmail,
        string $applicantEmail,
        string $applicantName,
        string $jobTitle,
        Carbon $startTime,
        int $durationMinutes,
        string $meetingLink,
        array $additionalDetails
    ): string {
        $endTime = $startTime->copy()->addMinutes($durationMinutes);

        return $this->calendarService->createInterviewEvent(
            $adminEmail,
            $applicantEmail,
            $applicantName,
            $jobTitle,
            $startTime,
            $endTime,
            $meetingLink,
            $additionalDetails
        );
    }

    /**
     * Get interview details for an application
     */
    public function getInterviewDetails(Application $application): ?array
    {
        if (!$application->interview_scheduled_at) {
            return null;
        }

        return [
            'scheduled_at' => $application->interview_scheduled_at,
            'duration_minutes' => $application->interview_duration_minutes,
            'meeting_link' => $application->interview_meeting_link,
            'meeting_provider' => $application->interview_meeting_provider,
            'interview_type' => $application->interview_type,
            'notes' => $application->interview_notes,
            'calendar_event_id' => $application->interview_calendar_event_id,
        ];
    }

    /**
     * Check if Google Calendar is properly configured
     */
    public function isGoogleCalendarConfigured(): bool
    {
        return $this->calendarService->isAuthenticated();
    }
}
