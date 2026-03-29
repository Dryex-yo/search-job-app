<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
        {
            \App\Models\Job::create([
                'title' => 'Full Stack Developer',
                'company_name' => 'DEVORYN TECH',
                'location' => 'Remote (Jakarta)',
                'salary' => 'Rp 15.000.000',
                'type' => 'Full-time',
                'description' => 'Membangun aplikasi masa depan dengan Laravel & Vue.'
            ]);

            \App\Models\Job::create([
                'title' => 'UI/UX Designer',
                'company_name' => 'GLASSCORP',
                'location' => 'Bandung',
                'salary' => 'Rp 10.000.000',
                'type' => 'Contract',
                'description' => 'Membuat desain glassmorphism yang cantik.'
            ]);
        }
}
