<?php

namespace App\Events;

use App\Models\Application;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InterviewScheduled
{
    use Dispatchable, SerializesModels;

    public Application $application;
    public array $interviewData;

    /**
     * Create a new event instance.
     */
    public function __construct(Application $application, array $interviewData)
    {
        $this->application = $application;
        $this->interviewData = $interviewData;
    }
}
