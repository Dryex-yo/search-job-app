<?php

namespace App\Console\Commands;

use App\Models\Application;
use Illuminate\Console\Command;

class FixEmptyCoverLetters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-empty-cover-letters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and report applications with empty or null cover letters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find applications with empty or null cover_letter
        $emptyApplications = Application::where(function ($query) {
            $query->whereNull('cover_letter')
                  ->orWhere('cover_letter', '=', '')
                  ->orWhere('cover_letter', '=', ' ');
        })
        ->with(['user', 'job'])
        ->get();

        if ($emptyApplications->isEmpty()) {
            $this->info('✓ Tidak ada aplikasi dengan cover_letter kosong.');
            return;
        }

        $this->warn('⚠️ Ditemukan ' . $emptyApplications->count() . ' aplikasi dengan cover_letter kosong:');
        $this->line(str_repeat('=', 100));

        foreach ($emptyApplications as $app) {
            $this->line('ID: ' . $app->id);
            $this->line('User: ' . ($app->user->name ?? 'Unknown'));
            $this->line('Job: ' . ($app->job->title ?? 'Unknown'));
            $this->line('Created At: ' . $app->created_at);
            $this->line('Status: ' . $app->status);
            $this->line(str_repeat('-', 100));
        }

        // Count summary
        $this->info('');
        $this->info('Summary:');
        $this->line('Total applications with empty cover_letter: ' . $emptyApplications->count());
        
        // Note: We're not deleting these automatically for data integrity
        $this->warn('Note: These applications were likely submitted before the fix was implemented.');
        $this->info('They should be reviewed manually. Users will need to resubmit their applications to add cover letters.');
    }
}
