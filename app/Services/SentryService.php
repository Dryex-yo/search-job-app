<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SentryErrorNotification;

/**
 * Sentry Error Tracking and Notification Service
 * 
 * Handles Sentry integration, error logging, and sending notifications
 * to Slack and Email when critical errors occur.
 */
class SentryService
{
    /**
     * Initialize Sentry with project configuration
     */
    public static function initialize()
    {
        if (!config('sentry.enabled')) {
            return;
        }

        try {
            \Sentry\init([
                'dsn' => config('sentry.dsn'),
                'environment' => config('sentry.environment'),
                'release' => config('sentry.release'),
                'traces_sample_rate' => config('sentry.tracing.traces_sample_rate', 1.0),
                'profiles_sample_rate' => config('sentry.profiles_sample_rate', 0),
                'attach_stacktrace' => config('sentry.attach_stacktrace', true),
                'send_default_pii' => config('sentry.send_default_pii', false),
            ]);

            Log::info('Sentry initialized successfully', [
                'environment' => config('sentry.environment'),
                'url' => config('app.url'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to initialize Sentry', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Capture and report an exception to Sentry
     */
    public static function captureException(\Throwable $exception, array $context = [])
    {
        if (!config('sentry.enabled')) {
            return $exception;
        }

        try {
            // Add context to Sentry
            if (!empty($context)) {
                \Sentry\withScope(function ($scope) use ($context) {
                    foreach ($context as $key => $value) {
                        $scope->setContext($key, $value);
                    }
                });
            }

            // Capture the exception
            \Sentry\captureException($exception);

            // Send notifications
            self::notifyError($exception, $context);
        } catch (\Exception $e) {
            Log::error('Error while capturing Sentry exception', [
                'error' => $e->getMessage(),
            ]);
        }

        return $exception;
    }

    /**
     * Capture and report a message to Sentry
     */
    public static function captureMessage(string $message, string $level = 'info', array $context = [])
    {
        if (!config('sentry.enabled')) {
            return;
        }

        try {
            // Map Laravel log levels to Sentry levels
            $sentryLevel = self::mapLogLevel($level);

            // Add context
            if (!empty($context)) {
                \Sentry\withScope(function ($scope) use ($context) {
                    foreach ($context as $key => $value) {
                        $scope->setContext($key, $value);
                    }
                });
            }

            // Capture the message
            \Sentry\captureMessage($message, $sentryLevel);
        } catch (\Exception $e) {
            Log::error('Error while capturing Sentry message', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Set user context for Sentry
     */
    public static function setUserContext($user)
    {
        if (!config('sentry.enabled')) {
            return;
        }

        try {
            \Sentry\setUser([
                'id' => $user->id ?? null,
                'email' => $user->email ?? null,
                'username' => $user->name ?? null,
            ]);

            Log::debug('Sentry user context set', [
                'user_id' => $user->id ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error while setting Sentry user context', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Set custom tag for Sentry
     */
    public static function setTag(string $key, string $value)
    {
        if (!config('sentry.enabled')) {
            return;
        }

        try {
            \Sentry\setTag($key, $value);
        } catch (\Exception $e) {
            Log::error('Error while setting Sentry tag', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Add breadcrumb to Sentry
     */
    public static function addBreadcrumb(string $message, array $data = [], string $category = 'app', string $level = 'info')
    {
        if (!config('sentry.enabled')) {
            return;
        }

        try {
            \Sentry\addBreadcrumb(
                new \Sentry\Breadcrumb(\Sentry\Breadcrumb::LEVEL_INFO, \Sentry\Breadcrumb::TYPE_DEFAULT, $category, $message, $data)
            );
        } catch (\Exception $e) {
            Log::error('Error while adding Sentry breadcrumb', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send error notifications to Slack and Email
     */
    protected static function notifyError(\Throwable $exception, array $context = [])
    {
        try {
            // Get admin email addresses
            $admins = \App\Models\User::where('role', 'admin')->pluck('email')->toArray();

            if (empty($admins)) {
                Log::warning('No admin users found for error notifications');
                return;
            }

            // Prepare error data
            $errorData = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'url' => config('app.url'),
                'environment' => config('app.env'),
                'timestamp' => now(),
                'context' => $context,
            ];

            // Send Slack notification if webhook is configured
            self::sendSlackNotification($errorData);

            // Send Email notification
            Notification::send($admins, new SentryErrorNotification($errorData));

            Log::info('Error notification sent', [
                'exception' => get_class($exception),
                'recipients' => count($admins),
            ]);
        } catch (\Exception $e) {
            Log::error('Error while sending error notifications', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send Slack notification for errors
     */
    protected static function sendSlackNotification(array $errorData)
    {
        $slackWebhook = config('services.slack.webhook_url') ?? env('SLACK_WEBHOOK_URL');

        if (empty($slackWebhook)) {
            return;
        }

        try {
            $title = "🚨 Error in " . config('app.name');
            $color = self::getSeverityColor($errorData['exception']);

            $payload = [
                'blocks' => [
                    [
                        'type' => 'header',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => $title,
                            'emoji' => true,
                        ],
                    ],
                    [
                        'type' => 'section',
                        'fields' => [
                            [
                                'type' => 'mrkdwn',
                                'text' => "*Exception:*\n" . $errorData['exception'],
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => "*Environment:*\n" . $errorData['environment'],
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => "*File:*\n" . $errorData['file'] . ':' . $errorData['line'],
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => "*Time:*\n" . $errorData['timestamp'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => "*Message:*\n```" . substr($errorData['message'], 0, 200) . "```",
                        ],
                    ],
                    [
                        'type' => 'divider',
                    ],
                    [
                        'type' => 'context',
                        'elements' => [
                            [
                                'type' => 'mrkdwn',
                                'text' => 'Sentry • ' . $errorData['url'],
                            ],
                        ],
                    ],
                ],
            ];

            $client = new \GuzzleHttp\Client();
            $client->post($slackWebhook, [
                'json' => $payload,
            ]);

            Log::info('Slack notification sent for error', [
                'exception' => $errorData['exception'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error while sending Slack notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Map Laravel log levels to Sentry levels
     */
    protected static function mapLogLevel(string $level): \Sentry\Severity
    {
        $mapping = [
            'debug' => \Sentry\Severity::DEBUG,
            'info' => \Sentry\Severity::INFO,
            'notice' => \Sentry\Severity::INFO,
            'warning' => \Sentry\Severity::WARNING,
            'error' => \Sentry\Severity::ERROR,
            'critical' => \Sentry\Severity::FATAL,
            'alert' => \Sentry\Severity::FATAL,
            'emergency' => \Sentry\Severity::FATAL,
        ];

        /** @var \Sentry\Severity $result */
        $result = $mapping[$level] ?? \Sentry\Severity::INFO;
        return $result;
    }

    /**
     * Get color based on exception type
     */
    protected static function getSeverityColor(string $exception): string
    {
        if (str_contains($exception, 'ValidationException')) {
            return '#FF9900';
        }
        if (str_contains($exception, 'AuthenticationException|AuthorizationException')) {
            return '#FF6B6B';
        }
        if (str_contains($exception, 'ModelNotFoundException|NotFoundHttpException')) {
            return '#4ECDC4';
        }
        return '#FF3333'; // Red for critical errors
    }
}
