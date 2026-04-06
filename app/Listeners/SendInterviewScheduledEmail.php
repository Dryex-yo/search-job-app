<?php

namespace App\Listeners;

use App\Events\InterviewScheduled;
use App\Mail\InterviewScheduledMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendInterviewScheduledEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(InterviewScheduled $event): void
    {
        try {
            if (!$event->application->tenant_id) {
                Log::warning('SendInterviewScheduledEmail: Application missing tenant_id', [
                    'application_id' => $event->application->id,
                ]);
                return;
            }

            $application = $event->application;
            
            // Send email to applicant
            if ($application->user && $application->user->email) {
                Mail::to($application->user->email)
                    ->send(new InterviewScheduledMail($application, 'applicant'));
            }

            // Send email to admin
            if ($application->admin && $application->admin->email) {
                Mail::to($application->admin->email)
                    ->send(new InterviewScheduledMail($application, 'admin'));
            }

            Log::info('Interview scheduled emails sent', [
                'application_id' => $application->id,
                'applicant_email' => $application->user->email ?? 'N/A',
                'admin_email' => $application->admin->email ?? 'N/A'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send interview scheduled email: ' . $e->getMessage());
            throw $e;
        }
    }
}
