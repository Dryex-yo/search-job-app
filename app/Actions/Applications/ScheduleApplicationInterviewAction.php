<?php

namespace App\Actions\Applications;

use App\Events\InterviewScheduled;
use App\Models\Application;
use App\Services\InterviewSchedulingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScheduleApplicationInterviewAction
{
    protected InterviewSchedulingService $schedulingService;

    public function __construct(InterviewSchedulingService $schedulingService)
    {
        $this->schedulingService = $schedulingService;
    }

    /**
     * Schedule an interview for an application
     * 
     * This action is triggered when admin changes application status to 'interview'
     * and provides interview details via the scheduling data
     */
    public function execute(
        Application $application,
        array $scheduleData = []
    ): bool {
        try {
            // If no schedule data provided, don't auto-schedule
            // Admin must explicitly provide interview details
            if (empty($scheduleData) || !isset($scheduleData['scheduled_at'])) {
                Log::info('Application status changed to interview, but no schedule data provided');
                return false;
            }

            // Schedule the interview
            $result = $this->schedulingService->scheduleInterview($application, $scheduleData);

            // Refresh the application to get the updated fields
            $application->refresh();

            // Dispatch event to trigger email notifications
            InterviewScheduled::dispatch($application, $result);

            Log::info('Interview scheduled successfully', [
                'application_id' => $application->id,
                'scheduled_at' => $scheduleData['scheduled_at']
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to schedule interview: ' . $e->getMessage(), [
                'application_id' => $application->id,
                'exception' => $e
            ]);
            throw $e;
        }
    }
}
