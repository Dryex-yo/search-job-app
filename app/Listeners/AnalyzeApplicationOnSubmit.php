<?php

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Jobs\AnalyzeApplicationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnalyzeApplicationOnSubmit implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ApplicationSubmitted $event): void
    {
        // Dispatch the analysis job after application is created
        AnalyzeApplicationJob::dispatch($event->application->id);
    }
}
