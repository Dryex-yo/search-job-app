<?php

namespace App\Events;

use App\Models\Application;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChanged implements ShouldBroadcast
{
    use Dispatchable, SerializesModels, InteractsWithBroadcasting;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Application $application,
        public string $previousStatus
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('applications'),
        ];
    }

    /**
     * Get the name of the broadcast event.
     */
    public function broadcastAs(): string
    {
        return 'application.status-changed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->application->id,
            'user_id' => $this->application->user_id,
            'job_id' => $this->application->job_id,
            'status' => $this->application->status,
            'previous_status' => $this->previousStatus,
            'user' => [
                'id' => $this->application->user->id,
                'name' => $this->application->user->name,
                'email' => $this->application->user->email,
            ],
            'job' => [
                'id' => $this->application->job->id,
                'title' => $this->application->job->title,
                'company' => $this->application->job->company,
            ],
        ];
    }
}
