<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private string $statusLabel;

    /**
     * Create a new message instance.
     */
    public function __construct(public Application $application)
    {
        $this->statusLabel = $this->getStatusLabel($application->status);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->application->user->email,
            subject: 'Update Status Lamaran Anda - ' . $this->statusLabel,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.application-status-changed',
            with: [
                'userName' => $this->application->user->name,
                'jobTitle' => $this->application->job->title,
                'companyName' => $this->application->job->company_name,
                'status' => $this->application->status,
                'statusLabel' => $this->statusLabel,
                'trackingUrl' => route('applications.track', ['id' => $this->application->id]),
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

    /**
     * Get human-readable status label.
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu Peninjauan',
            'reviewing' => 'Sedang Ditinjau',
            'shortlisted' => 'Lolos Seleksi Awal',
            'interviewed' => 'Dipanggil Wawancara',
            'rejected' => 'Ditolak',
            'accepted' => 'Diterima',
            default => ucfirst($status),
        };
    }
}
