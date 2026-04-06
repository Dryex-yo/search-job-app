<?php

namespace App\Jobs;

use App\Models\Application;
use App\Actions\Applications\AnalyzeApplicationAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Multitenancy\Jobs\TenantAware;
use Exception;
use Illuminate\Support\Facades\Log;

class AnalyzeApplicationJob implements ShouldQueue, TenantAware
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Application $application;
    
    // Retry configuration
    public $tries = 5;  // Increased to 5 retries
    public $backoff = [10, 20, 60, 120, 300];  // Exponential backoff: 10s, 20s, 1m, 2m, 5m
    public $timeout = 180;  // Timeout after 3 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Execute the job.
     */
    public function handle(AnalyzeApplicationAction $analyzeAction): void
    {
        try {
            $analyzeAction->execute($this->application);
        } catch (Exception $e) {
            Log::error('Failed to analyze application in queue', [
                'application_id' => $this->application->id,
                'error' => $e->getMessage()
            ]);
            
            // Update application status to failed
            $this->application->update(['ai_analysis_status' => 'failed']);
        }
    }
}
