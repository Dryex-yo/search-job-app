<?php

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Notifications\ApplicationReceivedNotification;
use Illuminate\Support\Facades\Log;

class SendApplicationReceivedEmail
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
    public function handle(ApplicationSubmitted $event): void
    {
        try {
            if (!$event->application->tenant_id) {
                Log::warning('SendApplicationReceivedEmail: Application missing tenant_id', [
                    'application_id' => $event->application->id,
                ]);
                return;
            }

            // Send email notification to the applicant
            $event->application->user->notify(
                new ApplicationReceivedNotification($event->application)
            );

            Log::info('Application received email sent', [
                'application_id' => $event->application->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send application received email: ' . $e->getMessage());
        }
    }
}
