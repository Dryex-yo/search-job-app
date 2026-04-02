<?php

namespace App\Console\Commands;

use App\Models\Application;
use Illuminate\Console\Command;

class CheckApplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-applications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check applications and their cover_letter content';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $applications = Application::with(['user', 'job'])->latest()->take(10)->get();

        $this->info('Recent Applications (Last 10):');
        $this->line(str_repeat('=', 120));

        foreach ($applications as $app) {
            $coverLetterLength = strlen($app->cover_letter ?? '');
            $coverLetterPreview = $coverLetterLength > 0 ? substr($app->cover_letter, 0, 60) . '...' : 'EMPTY/NULL';
            
            $this->line('ID: ' . $app->id);
            $this->line('User: ' . $app->user->name ?? 'Unknown');
            $this->line('Job: ' . $app->job->title ?? 'Unknown');
            $this->line('Resume Path: ' . ($app->resume_path ?? 'NULL'));
            $this->line("Cover Letter Length: {$coverLetterLength} characters");
            $this->line('Cover Letter Content: ' . $coverLetterPreview);
            $this->line('Created At: ' . $app->created_at);
            $this->line(str_repeat('-', 120));
        }
    }
}
