<?php

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Notifications\ApplicationReceivedNotification;

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
        // Send email notification to the applicant
        $event->application->user->notify(
            new ApplicationReceivedNotification($event->application)
        );
    }
}
