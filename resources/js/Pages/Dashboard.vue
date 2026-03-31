<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <UserLayout>
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Welcome Card -->
                <div class="lg:col-span-2 bg-white/5 border border-white/10 rounded-[2rem] p-8 backdrop-blur-xl">
                    <h2 class="text-3xl font-bold text-white mb-2">Welcome Back! 👋</h2>
                    <p class="text-slate-300 mb-6">Here's your job search dashboard overview</p>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-4 p-4 bg-cyan-500/10 border border-cyan-500/20 rounded-xl">
                            <span class="text-2xl">📋</span>
                            <div>
                                <h3 class="text-white font-semibold">Active Applications</h3>
                                <p class="text-slate-400 text-sm">You have 5 ongoing applications</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 bg-purple-500/10 border border-purple-500/20 rounded-xl">
                            <span class="text-2xl">⭐</span>
                            <div>
                                <h3 class="text-white font-semibold">Saved Jobs</h3>
                                <p class="text-slate-400 text-sm">You have 12 saved job openings</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 bg-green-500/10 border border-green-500/20 rounded-xl">
                            <span class="text-2xl">✅</span>
                            <div>
                                <h3 class="text-white font-semibold">Profile Completion</h3>
                                <p class="text-slate-400 text-sm">Your profile is 85% complete</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white/5 border border-white/10 rounded-[2rem] p-8 backdrop-blur-xl">
                    <h3 class="text-lg font-bold text-white mb-6">Quick Stats</h3>
                    <div class="space-y-4">
                        <div class="p-4 bg-white/5 rounded-lg border border-white/10">
                            <p class="text-slate-400 text-sm">Profile Views</p>
                            <p class="text-2xl font-bold text-cyan-400">342</p>
                        </div>
                        <div class="p-4 bg-white/5 rounded-lg border border-white/10">
                            <p class="text-slate-400 text-sm">Applications Sent</p>
                            <p class="text-2xl font-bold text-purple-400">15</p>
                        </div>
                        <div class="p-4 bg-white/5 rounded-lg border border-white/10">
                            <p class="text-slate-400 text-sm">Interviews Scheduled</p>
                            <p class="text-2xl font-bold text-green-400">3</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="mt-8 bg-white/5 border border-white/10 rounded-[2rem] p-8 backdrop-blur-xl">
                <h3 class="text-lg font-bold text-white mb-6">Recent Activity</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-3 border-b border-white/10 last:border-0">
                        <div>
                            <p class="text-white font-medium">Applied for Senior Frontend Developer</p>
                            <p class="text-slate-400 text-sm">2 hours ago</p>
                        </div>
                        <span class="px-3 py-1 bg-cyan-500/20 text-cyan-300 text-xs rounded-full">Applied</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-white/10 last:border-0">
                        <div>
                            <p class="text-white font-medium">Saved Backend Developer role at TechCorp</p>
                            <p class="text-slate-400 text-sm">Yesterday</p>
                        </div>
                        <span class="px-3 py-1 bg-purple-500/20 text-purple-300 text-xs rounded-full">Saved</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-white/10 last:border-0">
                        <div>
                            <p class="text-white font-medium">Interview scheduled - Full Stack Role</p>
                            <p class="text-slate-400 text-sm">3 days ago</p>
                        </div>
                        <span class="px-3 py-1 bg-green-500/20 text-green-300 text-xs rounded-full">Interview</span>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
