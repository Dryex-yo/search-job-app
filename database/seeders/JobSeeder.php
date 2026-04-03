<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobTitles = [
            // Developer Roles
            'Full Stack Developer', 'Backend Developer', 'Frontend Developer', 'Mobile Developer',
            'Junior Developer', 'Senior Developer', 'API Developer', 'Frontend Lead',
            'Backend Lead', 'Full Stack Lead', 'React Developer', 'Vue Developer',
            'Angular Developer', 'Node.js Developer', 'Python Developer', 'Java Developer',
            'C# Developer', '.NET Developer', 'PHP Developer', 'Ruby Developer',
            'Go Developer', 'Rust Developer', 'Swift Developer', 'Kotlin Developer',
            'Flutter Developer', 'Django Developer', 'Spring Boot Developer', 'Database Developer',
            
            // Design & UX
            'UI/UX Designer', 'Graphic Designer', 'Product Designer', 'UX Researcher',
            'Design Lead', 'Brand Manager', 'Web Designer', 'Motion Designer',
            'Interaction Designer', 'Design Consultant', 'Visual Designer', 'User Researcher',
            'Design System Lead', 'Accessibility Designer', 'Service Designer',
            
            // Data & AI
            'Data Scientist', 'Machine Learning Engineer', 'Data Analyst', 'Big Data Engineer',
            'AI Specialist', 'Data Engineer', 'Analytics Engineer', 'Business Intelligence',
            'Statistician', 'Data Visualizer', 'ML Operations Engineer', 'Data Pipeline Engineer',
            'LLM Engineer', 'Computer Vision Engineer', 'NLP Engineer',
            
            // DevOps & Infrastructure
            'DevOps Engineer', 'Cloud Architect', 'Database Administrator', 'System Administrator',
            'Security Engineer', 'Platform Engineer', 'Site Reliability Engineer', 'Network Engineer',
            'Infrastructure Engineer', 'Kubernetes Engineer', 'AWS Engineer', 'Azure Engineer',
            'GCP Engineer', 'Linux Administrator', 'Windows Administrator', 'Cloud Security Engineer',
            
            // QA & Testing
            'QA Engineer', 'Automation QA', 'QA Lead', 'Performance Tester',
            'Security Tester', 'Manual QA Tester', 'Test Automation Specialist', 'QA Analyst',
            
            // Management & Strategy
            'Product Manager', 'Business Analyst', 'Technical Director', 'Engineering Manager',
            'Solutions Architect', 'Software Architect', 'Operations Manager', 'Project Manager',
            'Scrum Master', 'Agile Coach', 'Tech Lead', 'VP Engineering',
            'CTO', 'Engineering Director', 'Technical Program Manager',
            
            // Specialized Tech
            'Blockchain Developer', 'Game Developer', 'AR/VR Developer', 'IoT Developer',
            'Embedded Systems Engineer', 'Robotics Engineer', 'Scripting Developer', 'DevSecOps Engineer',
            'Salesforce Developer', 'Shopify Developer', 'WordPress Developer', 'Custom Integration Developer',
        ];

        $companyNames = [
            'DEVORYN TECH', 'TECH CORP', 'DIGITAL SOLUTIONS', 'APP CREATORS',
            'STARTUP HUB', 'ENTERPRISE TECH', 'API SERVICES', 'FRONTEND MASTERS',
            'BACKEND EXPERTS', 'TECH LEADERS', 'GLASSCORP', 'CREATIVE AGENCY',
            'DESIGN STUDIO', 'RESEARCH LAB', 'DESIGN LEADERS', 'BRAND SOLUTIONS',
            'WEB DESIGN PRO', 'ANIMATION STUDIO', 'UX LABS', 'DESIGN CONSULTING',
            'DATA INSIGHTS', 'AI RESEARCH', 'ANALYTICS PRO', 'BIG DATA SOLUTIONS',
            'ARTIFICIAL INTELLIGENCE', 'DATA ENGINEERING', 'ANALYTICS ENGINEERING', 'BI SOLUTIONS',
            'STATISTICS LAB', 'DATA VIZ STUDIO', 'CLOUD SYSTEMS', 'CLOUD EXPERTS',
            'DATA MANAGEMENT', 'IT SERVICES', 'CYBER SECURITY', 'PLATFORM TECH',
            'RELIABILITY FIRST', 'NETWORK SOLUTIONS', 'INFRASTRUCTURE PRO', 'CONTAINER TECH',
            'TEST MASTERS', 'AUTOMATION TESTING', 'QA LEADERSHIP', 'PERFORMANCE TESTING',
            'SECURITY TESTING', 'PRODUCT LABS', 'CONSULT PRO', 'TECH DIRECTION',
            'TECH MANAGEMENT', 'SOLUTIONS INC', 'SOFTWARE DESIGN', 'OPERATIONS HUB',
            'PROJECT MASTERS', 'CRYPTO LABS', 'GAME STUDIO', 'IMMERSIVE TECH',
            'IOT SOLUTIONS', 'EMBEDDED TECH', 'ROBOTICS LABS', 'SCRIPTING SOLUTIONS',
            'NEXTGEN TECH', 'INNOVATE LABS', 'DIGITAL ERA', 'TECH FUTURES',
            'CODE MASTERS', 'DEVELOPMENT HOUSE', 'IT SOLUTIONS', 'TECH INNOVATIONS',
            'SOFTWARE HOUSE', 'INTEGRATION TECH', 'AUTOMATION SYSTEMS', 'CLOUD NATIVE',
            'TECH PIONEERS', 'DIGITAL TRANSFORMATION', 'SMART SYSTEMS', 'INTELLIGENCE AI',
            'QUANTUM TECH', 'APEX SOLUTIONS', 'ZENITH TECH', 'NOVA SYSTEMS',
        ];

        $locations = [
            'Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Medan',
            'Bali', 'Makassar', 'Semarang', 'Palembang', 'Tangerang',
            'Bekasi', 'Depok', 'Bogor', 'Cikarang', 'Serpong',
            'Remote', 'Remote (Jakarta)', 'Remote (Indonesia)', 'Hybrid Jakarta',
            'Hybrid Bandung', 'Hybrid Surabaya', 'Jakarta - Hybrid',
        ];

        $jobTypes = ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Temporary'];

        $salaryRanges = [
            'Rp 5.000.000', 'Rp 6.000.000', 'Rp 7.000.000', 'Rp 8.000.000', 'Rp 9.000.000',
            'Rp 10.000.000', 'Rp 11.000.000', 'Rp 12.000.000', 'Rp 13.000.000', 'Rp 14.000.000',
            'Rp 15.000.000', 'Rp 16.000.000', 'Rp 17.000.000', 'Rp 18.000.000', 'Rp 19.000.000',
            'Rp 20.000.000', 'Rp 21.000.000', 'Rp 22.000.000', 'Rp 25.000.000', 'Rp 30.000.000',
        ];

        $descriptions = [
            'Membangun aplikasi masa depan dengan teknologi terkini.',
            'Develop REST API dengan best practices dan clean code.',
            'Membuat UI yang responsif dan user-friendly.',
            'Mengembangkan aplikasi mobile yang powerful.',
            'Membantu development tim dalam berbagai project.',
            'Lead development team dan arsitektur sistem.',
            'Build dan maintain API services yang reliable.',
            'Lead tim dan code architecture yang baik.',
            'Design dan manage database yang scalable.',
            'Implementasi keamanan sistem yang ketat.',
            'Manage infrastructure dan deployment automation.',
            'Desain arsitektur cloud infrastructure.',
            'Manage database dan optimize queries.',
            'Maintain sistem dan network infrastructure.',
            'Create test automation dan scripts.',
            'Lead QA team dan quality strategy.',
            'Testing performa dan load testing.',
            'Security testing dan vulnerability assessment.',
            'Mengelola product development dan strategy.',
            'Analisis kebutuhan bisnis dan requirement gathering.',
            'Direktur teknis dan strategic planning.',
            'Manage tim engineering dan technical projects.',
            'Design solusi untuk klien enterprise.',
            'Design arsitektur software yang scalable.',
            'Manage operasional dan business processes.',
            'Manage project dan timeline delivery.',
            'Develop aplikasi blockchain dan smart contracts.',
            'Develop game menggunakan game engine terbaru.',
            'Create aplikasi augmented dan virtual reality.',
            'Develop Internet of Things applications.',
            'Develop embedded systems dan firmware.',
            'Design dan develop robotic systems.',
            'Develop scripts untuk automation.',
            'Analisis data dan machine learning untuk bisnis.',
            'Develop machine learning models dan algorithms.',
            'Analisis data dan business intelligence reporting.',
            'Handle dan process big data infrastructure.',
            'Develop artificial intelligence solutions.',
            'Build data pipelines dan ETL processes.',
            'Create analytics infrastructure dan dashboards.',
            'Develop BI tools dan dashboards untuk management.',
            'Analisis statistik dan forecasting data.',
            'Create data visualization dan infographics.',
            'Membuat desain glassmorphism yang cantik.',
            'Membuat visual design dan branding materials.',
            'Design product experience dan user interface.',
            'Research user behavior dan usability testing.',
            'Lead design team dan design strategy.',
            'Manage brand identity dan marketing assets.',
            'Desain website modern dan user-friendly.',
            'Create motion graphics dan animasi profesional.',
            'Design interactive experience untuk user.',
            'Konsultasi desain untuk berbagai klien.',
        ];

        $statuses = ['active', 'closed', 'inactive'];

        // Get all recruiters or create defaults
        $recruiters = User::role('recruiter')->limit(5)->get();
        
        if ($recruiters->isEmpty()) {
            // If no recruiters exist, create a few dummy recruiters
            $recruiters = collect();
            for ($i = 1; $i <= 5; $i++) {
                $recruiter = User::create([
                    'name' => "Recruiter $i",
                    'email' => "recruiter$i@example.com",
                    'password' => bcrypt('password'),
                ]);
                $recruiter->assignRole('recruiter');
                $recruiters->push($recruiter);
            }
        }

        // Create 1000 dummy jobs
        $batchSize = 100;
        for ($i = 0; $i < 1000; $i += $batchSize) {
            $jobs = [];
            for ($j = 0; $j < $batchSize && ($i + $j) < 1000; $j++) {
                $recruiter = $recruiters->random();
                
                $jobs[] = [
                    'title' => collect($jobTitles)->random(),
                    'company_name' => collect($companyNames)->random(),
                    'location' => collect($locations)->random(),
                    'salary' => collect($salaryRanges)->random(),
                    'type' => collect($jobTypes)->random(),
                    'description' => collect($descriptions)->random(),
                    'status' => collect($statuses)->random(),
                    'recruiter_id' => $recruiter->id,
                    'created_at' => now()->subDays(rand(0, 90)),
                    'updated_at' => now()->subDays(rand(0, 90)),
                ];
            }
            
            \App\Models\Job::insert($jobs);
        }
    }
}
