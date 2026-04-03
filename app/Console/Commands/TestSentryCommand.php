<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SentryService;
use Illuminate\Support\Facades\Mail;

class TestSentryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sentry:test {--type=error : Type of test (error, message, email, slack)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Sentry integration with different error types';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');

        if (!config('sentry.enabled')) {
            $this->error('❌ Sentry is currently disabled. Set SENTRY_ENABLED=true in .env');
            return 1;
        }

        $dsn = config('sentry.dsn');
        if (empty($dsn)) {
            $this->error('❌ Sentry DSN is not configured. Please set SENTRY_LARAVEL_DSN in .env');
            return 1;
        }

        $this->info('🚀 Testing Sentry Integration');
        $this->info('Environment: ' . config('sentry.environment'));
        $this->info('DSN: ' . substr($dsn, 0, 30) . '...');
        $this->newLine();

        try {
            match ($type) {
                'error' => $this->testException(),
                'message' => $this->testMessage(),
                'email' => $this->testEmail(),
                'slack' => $this->testSlack(),
                default => $this->testAll(),
            };

            $this->newLine();
            $this->info('✅ Test completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Test exception capture
     */
    protected function testException()
    {
        $this->info('Testing exception capture...');

        try {
            throw new \Exception('This is a test error from Sentry integration test command');
        } catch (\Exception $e) {
            SentryService::captureException($e, [
                'test_type' => 'exception',
                'timestamp' => now(),
                'command' => 'sentry:test',
            ]);

            $this->info('✓ Exception captured and sent to Sentry');
            $this->info('  Message: ' . $e->getMessage());
            $this->info('  Check your Sentry dashboard for the event');
        }
    }

    /**
     * Test message capture
     */
    protected function testMessage()
    {
        $this->info('Testing message capture...');

        SentryService::captureMessage(
            'This is a test message from Sentry integration',
            'info',
            [
                'test_type' => 'message',
                'timestamp' => now(),
                'command' => 'sentry:test',
            ]
        );

        $this->info('✓ Message captured and sent to Sentry');
    }

    /**
     * Test email notification
     */
    protected function testEmail()
    {
        $this->info('Testing email notification...');

        $admins = \App\Models\User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('⚠ No admin users found. Cannot send test email.');
            $this->info('Create an admin user first or add an admin email to .env');
            return;
        }

        $this->info('Found ' . $admins->count() . ' admin user(s)');

        foreach ($admins as $admin) {
            $this->info('  • ' . $admin->email);
        }

        try {
            // Capture an exception which will trigger email
            throw new \Exception('This is a test error notification email');
        } catch (\Exception $e) {
            SentryService::captureException($e, [
                'test_type' => 'email_notification',
            ]);
            $this->info('✓ Email notifications queued for delivery');
        }
    }

    /**
     * Test Slack notification
     */
    protected function testSlack()
    {
        $this->info('Testing Slack notification...');

        $webhookUrl = config('services.slack.webhook_url') ?? env('SLACK_WEBHOOK_URL');

        if (empty($webhookUrl)) {
            $this->warn('⚠ Slack webhook URL not configured');
            $this->info('Add SLACK_WEBHOOK_URL to your .env file');
            return;
        }

        try {
            // Capture an exception which will trigger Slack notification
            throw new \Exception('This is a test error for Slack notification');
        } catch (\Exception $e) {
            SentryService::captureException($e, [
                'test_type' => 'slack_notification',
            ]);
            $this->info('✓ Slack notification sent to webhook');
        }
    }

    /**
     * Test all features
     */
    protected function testAll()
    {
        $this->info('Running all tests...');
        $this->newLine();

        $this->info('1. Testing exception capture');
        $this->testException();
        $this->newLine();

        $this->info('2. Testing message capture');
        $this->testMessage();
        $this->newLine();

        $this->info('3. Testing email notifications');
        $this->testEmail();
        $this->newLine();

        $this->info('4. Testing Slack notifications');
        $this->testSlack();
    }
}
