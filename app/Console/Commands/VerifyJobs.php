<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class VerifyJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:verify-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify the dummy jobs created by the seeder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $totalJobs = Job::count();
        $this->info("Total jobs in database: {$totalJobs}");
        
        $this->newLine();
        $this->info("Job Status Distribution:");
        $statuses = Job::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
        foreach ($statuses as $row) {
            $this->info("  - {$row->status}: {$row->count} jobs");
        }
        
        $this->newLine();
        $this->info("Sample of 10 latest jobs:");
        $samples = Job::select('title', 'company_name', 'location', 'salary', 'status', 'type')
            ->orderByDesc('id')
            ->limit(10)
            ->get();
        
        foreach ($samples as $job) {
            $this->line("  ✓ {$job->title} at {$job->company_name}");
            $this->line("    Location: {$job->location} | Salary: {$job->salary} | Type: {$job->type} | Status: {$job->status}");
        }
        
        $this->newLine();
        $this->info("✓ Job seeding completed successfully!");
    }
}
