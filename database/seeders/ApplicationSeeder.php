<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Job;
use App\Models\Application;

class ApplicationSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     * Seed 10,000 dummy applications for stress testing
     */
    public function run(): void
    {
        echo "\n========== STARTING STRESS TEST DATA GENERATION ==========\n";

        // Step 1: Create 500 dummy applicant users
        echo "\n[1/3] Creating 500 dummy applicant users...\n";
        $applicantUsers = $this->createApplicantUsers(500);
        echo "✓ Created {$applicantUsers->count()} applicant users\n";

        // Step 2: Get all available jobs
        echo "\n[2/3] Fetching available jobs...\n";
        $jobs = Job::all();
        echo "✓ Found {$jobs->count()} available jobs\n";

        if ($jobs->isEmpty()) {
            echo "⚠ WARNING: No jobs found! Please run JobSeeder first.\n";
            echo "Run: php artisan db:seed --class=JobSeeder\n";
            return;
        }

        // Step 3: Create 10,000 dummy applications
        echo "\n[3/3] Creating 10,000 dummy applications...\n";
        $this->createApplications(10000, $applicantUsers, $jobs);
        echo "✓ Successfully created 10,000 dummy applications\n";

        // Summary Statistics
        $totalApplications = Application::count();
        $totalUsers = User::where('role', 'applicant')->count();
        
        echo "\n========== STRESS TEST DATA GENERATION COMPLETE ==========\n";
        echo "Summary Statistics:\n";
        echo "  - Total Applicant Users: {$totalUsers}\n";
        echo "  - Total Applications: {$totalApplications}\n";
        echo "  - Total Jobs: {$jobs->count()}\n";
        echo "  - Application Distribution:\n";

        $statuses = Application::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        foreach ($statuses as $status) {
            echo "    • {$status->status}: {$status->count}\n";
        }

        echo "\n========== READY FOR STRESS TEST ==========\n";
        echo "You can now test Search & Filtering performance at:\n";
        echo "  - Job Listing: /jobs\n";
        echo "  - Admin Applications: /admin/applications\n";
        echo "\nMeasure response times and database queries using:\n";
        echo "  - Laravel Debugbar\n";
        echo "  - Browser DevTools Network tab\n\n";
    }

    /**
     * Create dummy applicant users
     */
    private function createApplicantUsers(int $count): \Illuminate\Database\Eloquent\Collection
    {
        $batchSize = 100;

        for ($i = 0; $i < $count; $i += $batchSize) {
            $chunk = min($batchSize, $count - $i);
            $batchUsers = [];

            for ($j = 0; $j < $chunk; $j++) {
                $index = $i + $j + 1;
                $batchUsers[] = [
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'role' => 'applicant',
                    'phone' => fake()->phoneNumber(),
                    'created_at' => now()->subDays(rand(0, 90)),
                    'updated_at' => now()->subDays(rand(0, 90)),
                ];
            }

            User::insert($batchUsers);

            // Show progress
            echo "  ✓ Created batch " . ceil(($i + $chunk) / $batchSize) . "/" . ceil($count / $batchSize) . "\n";
        }

        // Return all applicant users that were just created
        return User::where('role', 'applicant')->get();
    }

    /**
     * Create 10,000 dummy applications
     */
    private function createApplications(int $count, $applicantUsers, $jobs): void
    {
        $statuses = ['pending', 'shortlisted', 'rejected', 'accepted'];
        $batchSize = 500;

        for ($i = 0; $i < $count; $i += $batchSize) {
            $chunk = min($batchSize, $count - $i);
            $applications = [];

            for ($j = 0; $j < $chunk; $j++) {
                $user = $applicantUsers->random();
                $job = $jobs->random();

                $applications[] = [
                    'job_id' => $job->id,
                    'user_id' => $user->id,
                    'resume_path' => 'dummy/resume_' . uniqid() . '.pdf',
                    'cover_letter' => fake()->paragraph(),
                    'status' => collect($statuses)->random(),
                    'created_at' => now()->subDays(rand(0, 90)),
                    'updated_at' => now()->subDays(rand(0, 90)),
                ];
            }

            Application::insert($applications);

            // Progress indicator
            $percentage = min(100, round(($i + $chunk) / $count * 100));
            $bar = str_repeat('█', round($percentage / 5)) . str_repeat('░', 20 - round($percentage / 5));
            $progress = $i + $chunk;
            echo "  [$bar] $percentage% ($progress/$count)\n";
        }
    }
}

