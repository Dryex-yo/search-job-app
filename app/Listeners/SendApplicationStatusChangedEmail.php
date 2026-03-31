<?php

namespace App\Listeners;

use App\Events\ApplicationStatusChanged;
use App\Notifications\ApplicationStatusChangedNotification;

class SendApplicationStatusChangedEmail
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
    public function handle(ApplicationStatusChanged $event): void
    {
        // Send email notification to the applicant about status change
        $event->application->user->notify(
            new ApplicationStatusChangedNotification(
                $event->application,
                $event->previousStatus
            )
        );
    }
}
