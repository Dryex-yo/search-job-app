<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SentryErrorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $errorData;

    public function __construct(array $errorData)
    {
        $this->errorData = $errorData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $errorMessage = $this->errorData['message'] ?? 'Unknown error';
        $exceptionType = $this->errorData['exception'] ?? 'Exception';
        $file = $this->errorData['file'] ?? 'Unknown file';
        $line = $this->errorData['line'] ?? 'Unknown line';

        $message = (new MailMessage)
            ->subject('🚨 Error Report: ' . config('app.name'))
            ->priority('high')
            ->greeting('Hello ' . ($notifiable->name ?? 'Admin') . ',')
            ->line('An error has been detected in ' . config('app.name') . ' during execution.')
            ->line('')
            ->line('**Error Details:**')
            ->line('Exception Type: ' . $exceptionType)
            ->line('Message: ' . $errorMessage)
            ->line('File: ' . $file . ' (Line ' . $line . ')')
            ->line('Environment: ' . $this->errorData['environment'])
            ->line('Time: ' . $this->errorData['timestamp'])
            ->line('')
            ->line('**Stack Trace:**')
            ->line('```')
            ->line(substr($this->errorData['trace'], 0, 1000))
            ->line('```')
            ->line('');

        if (isset($this->errorData['context']) && !empty($this->errorData['context'])) {
            $message->line('**Additional Context:**')
                ->line('```')
                ->line(json_encode($this->errorData['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                ->line('```')
                ->line('');
        }

        return $message->action('View in Sentry', url('/'))
            ->line('If you need more details, please check the Sentry dashboard or application logs.')
            ->salutation('Best regards, ' . config('app.name') . ' Error Tracking System');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'exception' => $this->errorData['exception'],
            'message' => $this->errorData['message'],
            'file' => $this->errorData['file'],
            'line' => $this->errorData['line'],
            'environment' => $this->errorData['environment'],
        ];
    }
}
