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
                ['Full Stack Developer', 'DEVORYN TECH', 'Remote (Jakarta)', 'Rp 15.000.000', 'Full-time', 'Membangun aplikasi masa depan dengan Laravel & Vue.'],
                ['Backend Developer', 'TECH CORP', 'Jakarta', 'Rp 14.000.000', 'Full-time', 'Develop REST API dengan PHP dan Node.js.'],
                ['Frontend Developer', 'DIGITAL SOLUTIONS', 'Bandung', 'Rp 12.000.000', 'Full-time', 'Membuat UI yang responsif menggunakan React dan Vue.'],
                ['Mobile Developer', 'APP CREATORS', 'Remote', 'Rp 13.000.000', 'Full-time', 'Mengembangkan aplikasi Android dan iOS native.'],
                ['UI/UX Designer', 'GLASSCORP', 'Bandung', 'Rp 10.000.000', 'Contract', 'Membuat desain glassmorphism yang cantik.'],
                ['Data Scientist', 'DATA INSIGHTS', 'Jakarta', 'Rp 16.000.000', 'Full-time', 'Analisis data dan machine learning untuk bisnis.'],
                ['DevOps Engineer', 'CLOUD SYSTEMS', 'Remote', 'Rp 14.500.000', 'Full-time', 'Manage infrastructure dan deployment automation.'],
                ['QA Engineer', 'TEST MASTERS', 'Surabaya', 'Rp 9.000.000', 'Full-time', 'Melakukan testing dan quality assurance.'],
                ['Product Manager', 'PRODUCT LABS', 'Jakarta', 'Rp 17.000.000', 'Full-time', 'Mengelola product development dan strategy.'],
                ['Business Analyst', 'CONSULT PRO', 'Jakarta', 'Rp 11.000.000', 'Full-time', 'Analisis kebutuhan bisnis dan requirement gathering.'],
                ['Junior Developer', 'STARTUP HUB', 'Remote', 'Rp 8.000.000', 'Full-time', 'Membantu development tim dalam berbagai project.'],
                ['Senior Developer', 'ENTERPRISE TECH', 'Jakarta', 'Rp 20.000.000', 'Full-time', 'Lead development team dan arsitektur sistem.'],
                ['Graphic Designer', 'CREATIVE AGENCY', 'Bandung', 'Rp 9.500.000', 'Full-time', 'Membuat visual design dan branding materials.'],
                ['Content Writer', 'MEDIA COMPANY', 'Remote', 'Rp 7.000.000', 'Part-time', 'Menulis artikel dan konten untuk website.'],
                ['Social Media Manager', 'SOCIAL LABS', 'Jakarta', 'Rp 8.500.000', 'Full-time', 'Kelola media sosial dan community engagement.'],
                ['Marketing Specialist', 'MARKETING PRO', 'Jakarta', 'Rp 10.500.000', 'Full-time', 'Strategi marketing dan campaign execution.'],
                ['Sales Executive', 'SALES CORP', 'Jakarta', 'Rp 11.500.000', 'Full-time', 'Mencari dan membangun klien baru.'],
                ['HR Manager', 'HUMAN CAPITAL', 'Jakarta', 'Rp 12.000.000', 'Full-time', 'Mengelola SDM dan recruitment.'],
                ['Finance Analyst', 'FINANCE SOLUTIONS', 'Jakarta', 'Rp 13.000.000', 'Full-time', 'Analisis finansial dan laporan keuangan.'],
                ['Security Engineer', 'CYBER SECURITY', 'Remote', 'Rp 15.500.000', 'Full-time', 'Implementasi dan monitoring keamanan sistem.'],
                ['Cloud Architect', 'CLOUD EXPERTS', 'Jakarta', 'Rp 18.000.000', 'Full-time', 'Desain arsitektur cloud infrastructure.'],
                ['Database Administrator', 'DATA MANAGEMENT', 'Jakarta', 'Rp 13.500.000', 'Full-time', 'Manage database dan optimize queries.'],
                ['System Administrator', 'IT SERVICES', 'Jakarta', 'Rp 10.000.000', 'Full-time', 'Maintain sistem dan network infrastructure.'],
                ['Customer Support', 'SUPPORT CENTER', 'Jakarta', 'Rp 6.500.000', 'Full-time', 'Memberikan support kepada customer.'],
                ['Technical Writer', 'DOCUMENTATION PRO', 'Remote', 'Rp 8.000.000', 'Full-time', 'Menulis dokumentasi teknis produk.'],
                ['Solutions Architect', 'SOLUTIONS INC', 'Jakarta', 'Rp 16.500.000', 'Full-time', 'Design solusi untuk klien enterprise.'],
                ['Machine Learning Engineer', 'AI RESEARCH', 'Remote', 'Rp 17.500.000', 'Full-time', 'Develop machine learning models dan algorithms.'],
                ['Blockchain Developer', 'CRYPTO LABS', 'Remote', 'Rp 18.500.000', 'Full-time', 'Develop aplikasi blockchain dan smart contracts.'],
                ['Game Developer', 'GAME STUDIO', 'Bandung', 'Rp 12.500.000', 'Full-time', 'Develop game menggunakan Unity dan Unreal.'],
                ['AR/VR Developer', 'IMMERSIVE TECH', 'Jakarta', 'Rp 14.000.000', 'Full-time', 'Create aplikasi augmented dan virtual reality.'],
                ['API Developer', 'API SERVICES', 'Remote', 'Rp 13.000.000', 'Full-time', 'Build dan maintain API services.'],
                ['Frontend Lead', 'FRONTEND MASTERS', 'Jakarta', 'Rp 16.000.000', 'Full-time', 'Lead tim frontend dan code architecture.'],
                ['Backend Lead', 'BACKEND EXPERTS', 'Jakarta', 'Rp 16.000.000', 'Full-time', 'Lead tim backend dan database design.'],
                ['Full Stack Lead', 'TECH LEADERS', 'Jakarta', 'Rp 19.000.000', 'Full-time', 'Lead full stack development team.'],
                ['Technical Director', 'TECH DIRECTION', 'Jakarta', 'Rp 21.000.000', 'Full-time', 'Direktur teknis dan strategic planning.'],
                ['Software Architect', 'SOFTWARE DESIGN', 'Jakarta', 'Rp 19.500.000', 'Full-time', 'Design arsitektur software yang scalable.'],
                ['Performance Engineer', 'PERFORMANCE LABS', 'Remote', 'Rp 14.500.000', 'Full-time', 'Optimize aplikasi untuk performa maksimal.'],
                ['Automation Engineer', 'AUTOMATION SOLUTIONS', 'Jakarta', 'Rp 13.000.000', 'Full-time', 'Build automatisasi untuk proses bisnis.'],
                ['Integration Engineer', 'INTEGRATION EXPERTS', 'Jakarta', 'Rp 12.500.000', 'Full-time', 'Integrate berbagai sistem dan aplikasi.'],
                ['Platform Engineer', 'PLATFORM TECH', 'Remote', 'Rp 15.000.000', 'Full-time', 'Build platform infrastructure untuk developer.'],
                ['Site Reliability Engineer', 'RELIABILITY FIRST', 'Remote', 'Rp 16.000.000', 'Full-time', 'Maintain reliability dan uptime sistem.'],
                ['Engineering Manager', 'TECH MANAGEMENT', 'Jakarta', 'Rp 18.000.000', 'Full-time', 'Manage tim engineering dan technical projects.'],
                ['Product Designer', 'DESIGN STUDIO', 'Jakarta', 'Rp 11.000.000', 'Full-time', 'Design product experience dan user interface.'],
                ['UX Researcher', 'RESEARCH LAB', 'Jakarta', 'Rp 10.500.000', 'Full-time', 'Research user behavior dan usability testing.'],
                ['Design Lead', 'DESIGN LEADERS', 'Jakarta', 'Rp 14.000.000', 'Full-time', 'Lead design team dan design strategy.'],
                ['Brand Manager', 'BRAND SOLUTIONS', 'Jakarta', 'Rp 12.000.000', 'Full-time', 'Manage brand identity dan marketing assets.'],
                ['Growth Hacker', 'GROWTH LABS', 'Remote', 'Rp 11.000.000', 'Full-time', 'Strategi growth dan user acquisition.'],
                ['Partnership Manager', 'PARTNERSHIPS INC', 'Jakarta', 'Rp 11.500.000', 'Full-time', 'Develop strategic partnerships dan collaborations.'],
                ['Operations Manager', 'OPERATIONS HUB', 'Jakarta', 'Rp 11.000.000', 'Full-time', 'Manage operasional dan business processes.'],
                ['Project Manager', 'PROJECT MASTERS', 'Jakarta', 'Rp 12.500.000', 'Full-time', 'Manage project dan timeline delivery.'],
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
