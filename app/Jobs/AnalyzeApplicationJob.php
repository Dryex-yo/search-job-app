<?php

namespace App\Jobs;

use App\Models\Application;
use App\Actions\Applications\AnalyzeApplicationAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;
use Illuminate\Support\Facades\Log;

class AnalyzeApplicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $applicationId;
    
    // Retry configuration
    public $tries = 5;  // Increased to 5 retries
    public $backoff = [10, 20, 60, 120, 300];  // Exponential backoff: 10s, 20s, 1m, 2m, 5m
    public $timeout = 180;  // Timeout after 3 minutes

    /**
     * Create a new job instance.
     */
    public function __construct($applicationId)
    {
        $this->applicationId = $applicationId;
    }

    /**
     * Execute the job.
     */
    public function handle(AnalyzeApplicationAction $analyzeAction): void
    {
        try {
            $application = Application::findOrFail($this->applicationId);
            $analyzeAction->execute($application);
        } catch (Exception $e) {
            Log::error('Failed to analyze application in queue', [
                'application_id' => $this->applicationId,
                'error' => $e->getMessage()
            ]);
            
            // Update application status to failed
            if (isset($application)) {
                $application->update(['ai_analysis_status' => 'failed']);
            }
        }
    }
}
