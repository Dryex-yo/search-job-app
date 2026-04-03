<?php

namespace App\Events;

use App\Models\Application;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InterviewScheduled
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

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

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [];
    }
}
