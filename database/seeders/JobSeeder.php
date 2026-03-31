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
            $jobs = [
                // Tier 1: Developer Roles (10 jobs)
                ['Full Stack Developer', 'DEVORYN TECH', 'Remote (Jakarta)', 'Rp 15.000.000', 'Full-time', 'Membangun aplikasi masa depan dengan Laravel & Vue.'],
                ['Backend Developer', 'TECH CORP', 'Jakarta', 'Rp 14.000.000', 'Full-time', 'Develop REST API dengan PHP dan Node.js.'],
                ['Frontend Developer', 'DIGITAL SOLUTIONS', 'Bandung', 'Rp 12.000.000', 'Full-time', 'Membuat UI yang responsif menggunakan React dan Vue.'],
                ['Mobile Developer', 'APP CREATORS', 'Remote', 'Rp 13.000.000', 'Full-time', 'Mengembangkan aplikasi Android dan iOS native.'],
                ['Junior Developer', 'STARTUP HUB', 'Remote', 'Rp 8.000.000', 'Full-time', 'Membantu development tim dalam berbagai project.'],
                ['Senior Developer', 'ENTERPRISE TECH', 'Jakarta', 'Rp 20.000.000', 'Full-time', 'Lead development team dan arsitektur sistem.'],
                ['API Developer', 'API SERVICES', 'Remote', 'Rp 13.000.000', 'Full-time', 'Build dan maintain API services.'],
                ['Frontend Lead', 'FRONTEND MASTERS', 'Jakarta', 'Rp 16.000.000', 'Full-time', 'Lead tim frontend dan code architecture.'],
                ['Backend Lead', 'BACKEND EXPERTS', 'Jakarta', 'Rp 16.000.000', 'Full-time', 'Lead tim backend dan database design.'],
                ['Full Stack Lead', 'TECH LEADERS', 'Jakarta', 'Rp 19.000.000', 'Full-time', 'Lead full stack development team.'],

                // Tier 2: Design & UX (10 jobs)
                ['UI/UX Designer', 'GLASSCORP', 'Bandung', 'Rp 10.000.000', 'Contract', 'Membuat desain glassmorphism yang cantik.'],
                ['Graphic Designer', 'CREATIVE AGENCY', 'Bandung', 'Rp 9.500.000', 'Full-time', 'Membuat visual design dan branding materials.'],
                ['Product Designer', 'DESIGN STUDIO', 'Jakarta', 'Rp 11.000.000', 'Full-time', 'Design product experience dan user interface.'],
                ['UX Researcher', 'RESEARCH LAB', 'Jakarta', 'Rp 10.500.000', 'Full-time', 'Research user behavior dan usability testing.'],
                ['Design Lead', 'DESIGN LEADERS', 'Jakarta', 'Rp 14.000.000', 'Full-time', 'Lead design team dan design strategy.'],
                ['Brand Manager', 'BRAND SOLUTIONS', 'Jakarta', 'Rp 12.000.000', 'Full-time', 'Manage brand identity dan marketing assets.'],
                ['Web Designer', 'WEB DESIGN PRO', 'Surabaya', 'Rp 9.000.000', 'Full-time', 'Desain website modern dan user-friendly.'],
                ['Motion Designer', 'ANIMATION STUDIO', 'Jakarta', 'Rp 11.500.000', 'Full-time', 'Create motion graphics dan animasi profesional.'],
                ['Interaction Designer', 'UX LABS', 'Remote', 'Rp 10.800.000', 'Full-time', 'Design interactive experience untuk user.'],
                ['Design Consultant', 'DESIGN CONSULTING', 'Jakarta', 'Rp 13.000.000', 'Part-time', 'Konsultasi desain untuk berbagai klien.'],

                // Tier 3: Data & AI (10 jobs)
                ['Data Scientist', 'DATA INSIGHTS', 'Jakarta', 'Rp 16.000.000', 'Full-time', 'Analisis data dan machine learning untuk bisnis.'],
                ['Machine Learning Engineer', 'AI RESEARCH', 'Remote', 'Rp 17.500.000', 'Full-time', 'Develop machine learning models dan algorithms.'],
                ['Data Analyst', 'ANALYTICS PRO', 'Jakarta', 'Rp 11.000.000', 'Full-time', 'Analisis data dan business intelligence reporting.'],
                ['Big Data Engineer', 'BIG DATA SOLUTIONS', 'Jakarta', 'Rp 15.500.000', 'Full-time', 'Handle dan process big data infrastructure.'],
                ['AI Specialist', 'ARTIFICIAL INTELLIGENCE', 'Remote', 'Rp 18.000.000', 'Full-time', 'Develop artificial intelligence solutions.'],
                ['Data Engineer', 'DATA ENGINEERING', 'Jakarta', 'Rp 14.500.000', 'Full-time', 'Build data pipelines dan ETL processes.'],
                ['Analytics Engineer', 'ANALYTICS ENGINEERING', 'Remote', 'Rp 12.500.000', 'Full-time', 'Create analytics infrastructure dan dashboards.'],
                ['Business Intelligence', 'BI SOLUTIONS', 'Jakarta', 'Rp 12.000.000', 'Full-time', 'Develop BI tools dan dashboards untuk management.'],
                ['Statistician', 'STATISTICS LAB', 'Jakarta', 'Rp 11.000.000', 'Full-time', 'Analisis statistik dan forecasting data.'],
                ['Data Visualizer', 'DATA VIZ STUDIO', 'Bandung', 'Rp 10.500.000', 'Full-time', 'Create data visualization dan infographics.'],

                // Tier 4: DevOps & Infrastructure (10 jobs)
                ['DevOps Engineer', 'CLOUD SYSTEMS', 'Remote', 'Rp 14.500.000', 'Full-time', 'Manage infrastructure dan deployment automation.'],
                ['Cloud Architect', 'CLOUD EXPERTS', 'Jakarta', 'Rp 18.000.000', 'Full-time', 'Desain arsitektur cloud infrastructure.'],
                ['Database Administrator', 'DATA MANAGEMENT', 'Jakarta', 'Rp 13.500.000', 'Full-time', 'Manage database dan optimize queries.'],
                ['System Administrator', 'IT SERVICES', 'Jakarta', 'Rp 10.000.000', 'Full-time', 'Maintain sistem dan network infrastructure.'],
                ['Security Engineer', 'CYBER SECURITY', 'Remote', 'Rp 15.500.000', 'Full-time', 'Implementasi dan monitoring keamanan sistem.'],
                ['Platform Engineer', 'PLATFORM TECH', 'Remote', 'Rp 15.000.000', 'Full-time', 'Build platform infrastructure untuk developer.'],
                ['Site Reliability Engineer', 'RELIABILITY FIRST', 'Remote', 'Rp 16.000.000', 'Full-time', 'Maintain reliability dan uptime sistem.'],
                ['Network Engineer', 'NETWORK SOLUTIONS', 'Jakarta', 'Rp 12.000.000', 'Full-time', 'Design dan manage network infrastructure.'],
                ['Infrastructure Engineer', 'INFRASTRUCTURE PRO', 'Remote', 'Rp 13.000.000', 'Full-time', 'Build infrastructure scalable dan reliable.'],
                ['Kubernetes Engineer', 'CONTAINER TECH', 'Remote', 'Rp 14.000.000', 'Full-time', 'Manage dan optimize kubernetes clusters.'],

                // Tier 5: QA & Testing (5 jobs)
                ['QA Engineer', 'TEST MASTERS', 'Surabaya', 'Rp 9.000.000', 'Full-time', 'Melakukan testing dan quality assurance.'],
                ['Automation QA', 'AUTOMATION TESTING', 'Jakarta', 'Rp 10.000.000', 'Full-time', 'Create test automation dan scripts.'],
                ['QA Lead', 'QA LEADERSHIP', 'Jakarta', 'Rp 12.000.000', 'Full-time', 'Lead QA team dan quality strategy.'],
                ['Performance Tester', 'PERFORMANCE TESTING', 'Remote', 'Rp 11.000.000', 'Full-time', 'Testing performa dan load testing.'],
                ['Security Tester', 'SECURITY TESTING', 'Jakarta', 'Rp 11.500.000', 'Full-time', 'Security testing dan vulnerability assessment.'],

                // Tier 6: Management & Strategy (8 jobs)
                ['Product Manager', 'PRODUCT LABS', 'Jakarta', 'Rp 17.000.000', 'Full-time', 'Mengelola product development dan strategy.'],
                ['Business Analyst', 'CONSULT PRO', 'Jakarta', 'Rp 11.000.000', 'Full-time', 'Analisis kebutuhan bisnis dan requirement gathering.'],
                ['Technical Director', 'TECH DIRECTION', 'Jakarta', 'Rp 21.000.000', 'Full-time', 'Direktur teknis dan strategic planning.'],
                ['Engineering Manager', 'TECH MANAGEMENT', 'Jakarta', 'Rp 18.000.000', 'Full-time', 'Manage tim engineering dan technical projects.'],
                ['Solutions Architect', 'SOLUTIONS INC', 'Jakarta', 'Rp 16.500.000', 'Full-time', 'Design solusi untuk klien enterprise.'],
                ['Software Architect', 'SOFTWARE DESIGN', 'Jakarta', 'Rp 19.500.000', 'Full-time', 'Design arsitektur software yang scalable.'],
                ['Operations Manager', 'OPERATIONS HUB', 'Jakarta', 'Rp 11.000.000', 'Full-time', 'Manage operasional dan business processes.'],
                ['Project Manager', 'PROJECT MASTERS', 'Jakarta', 'Rp 12.500.000', 'Full-time', 'Manage project dan timeline delivery.'],

                // Tier 7: Specialized Tech (7 jobs)
                ['Blockchain Developer', 'CRYPTO LABS', 'Remote', 'Rp 18.500.000', 'Full-time', 'Develop aplikasi blockchain dan smart contracts.'],
                ['Game Developer', 'GAME STUDIO', 'Bandung', 'Rp 12.500.000', 'Full-time', 'Develop game menggunakan Unity dan Unreal.'],
                ['AR/VR Developer', 'IMMERSIVE TECH', 'Jakarta', 'Rp 14.000.000', 'Full-time', 'Create aplikasi augmented dan virtual reality.'],
                ['IoT Developer', 'IOT SOLUTIONS', 'Surabaya', 'Rp 12.000.000', 'Full-time', 'Develop Internet of Things applications.'],
                ['Embedded Systems', 'EMBEDDED TECH', 'Jakarta', 'Rp 13.000.000', 'Full-time', 'Develop embedded systems dan firmware.'],
                ['Robotics Engineer', 'ROBOTICS LABS', 'Remote', 'Rp 15.000.000', 'Full-time', 'Design dan develop robotic systems.'],
                ['Scripting Developer', 'SCRIPTING SOLUTIONS', 'Remote', 'Rp 9.500.000', 'Full-time', 'Develop scripts untuk automation.'],
            ];

            foreach ($jobs as $job) {
                \App\Models\Job::create([
                    'title' => $job[0],
                    'company_name' => $job[1],
                    'location' => $job[2],
                    'salary' => $job[3],
                    'type' => $job[4],
                    'description' => $job[5],
                ]);
            }
        }
}
