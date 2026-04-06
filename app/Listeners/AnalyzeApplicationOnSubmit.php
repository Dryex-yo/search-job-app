<?php

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Jobs\AnalyzeApplicationJob;
use Illuminate\Support\Facades\Log;

class AnalyzeApplicationOnSubmit
{
    /**
     * Handle the event.
     */
    public function handle(ApplicationSubmitted $event): void
    {
        try {
            if (!$event->application->tenant_id) {
                Log::warning('AnalyzeApplicationOnSubmit: Application missing tenant_id', [
                    'application_id' => $event->application->id,
                ]);
                return;
            }

            // Dispatch the analysis job - this job IS TenantAware
            AnalyzeApplicationJob::dispatch($event->application);

            Log::info('Application analysis job dispatched', [
                'application_id' => $event->application->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch analysis job: ' . $e->getMessage(), [
                'application_id' => $event->application->id,
            ]);
        }
    }
}
