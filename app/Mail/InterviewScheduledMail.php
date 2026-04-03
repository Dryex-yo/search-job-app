<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewScheduledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Application $application;
    public string $recipientType; // 'applicant' or 'admin'

    /**
     * Create a new message instance.
     */
    public function __construct(Application $application, string $recipientType = 'applicant')
    {
        $this->application = $application;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->recipientType) {
            'admin' => "Interview Scheduled: {$this->application->user->name} for {$this->application->job->title}",
            'applicant' => "Your Interview is Scheduled - {$this->application->job->title}",
            default => "Interview Scheduled"
        };

        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.interview-scheduled',
            with: [
                'application' => $this->application,
                'recipientType' => $this->recipientType,
                'applicantName' => $this->application->user->name,
                'jobTitle' => $this->application->job->title,
                'interviewDateTime' => $this->application->interview_scheduled_at,
                'duration' => $this->application->interview_duration_minutes,
                'meetingLink' => $this->application->interview_meeting_link,
                'meetingProvider' => $this->application->interview_meeting_provider,
                'interviewType' => $this->application->interview_type,
                'notes' => $this->application->interview_notes,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
