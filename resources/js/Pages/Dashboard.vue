<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <UserLayout>
        <div class="max-w-7xl mx-auto p-4 sm:p-6 md:p-8">
            <!-- Main Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
                <!-- Welcome Card -->
                <div class="lg:col-span-2 bg-white/5 border border-white/10 rounded-lg sm:rounded-2xl md:rounded-[2rem] p-4 sm:p-6 md:p-8 backdrop-blur-xl">
                    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">Welcome Back! 👋</h2>
                    <p class="text-sm sm:text-base text-slate-300 mb-4 sm:mb-6">Here's your job search dashboard overview</p>
                    
                    <div class="space-y-3 sm:space-y-4">
                        <!-- Active Applications Card -->
                        <div class="flex items-start gap-3 sm:gap-4 p-3 sm:p-4 bg-cyan-500/10 border border-cyan-500/20 rounded-lg sm:rounded-xl">
                            <span class="text-xl sm:text-2xl flex-shrink-0">📋</span>
                            <div class="min-w-0">
                                <h3 class="text-white font-semibold text-sm sm:text-base">Active Applications</h3>
                                <p class="text-slate-400 text-xs sm:text-sm">You have 5 ongoing applications</p>
                            </div>
                        </div>

                        <!-- Saved Jobs Card -->
                        <div class="flex items-start gap-3 sm:gap-4 p-3 sm:p-4 bg-purple-500/10 border border-purple-500/20 rounded-lg sm:rounded-xl">
                            <span class="text-xl sm:text-2xl flex-shrink-0">⭐</span>
                            <div class="min-w-0">
                                <h3 class="text-white font-semibold text-sm sm:text-base">Saved Jobs</h3>
                                <p class="text-slate-400 text-xs sm:text-sm">You have 12 saved job openings</p>
                            </div>
                        </div>

                        <!-- Profile Completion Card -->
                        <div class="flex items-start gap-3 sm:gap-4 p-3 sm:p-4 bg-green-500/10 border border-green-500/20 rounded-lg sm:rounded-xl">
                            <span class="text-xl sm:text-2xl flex-shrink-0">✅</span>
                            <div class="min-w-0">
                                <h3 class="text-white font-semibold text-sm sm:text-base">Profile Completion</h3>
                                <p class="text-slate-400 text-xs sm:text-sm">Your profile is 85% complete</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white/5 border border-white/10 rounded-lg sm:rounded-2xl md:rounded-[2rem] p-4 sm:p-6 md:p-8 backdrop-blur-xl">
                    <h3 class="text-lg sm:text-xl font-bold text-white mb-4 sm:mb-6">Quick Stats</h3>
                    <div class="space-y-3 sm:space-y-4">
                        <!-- Profile Views -->
                        <div class="p-3 sm:p-4 bg-white/5 rounded-lg border border-white/10">
                            <p class="text-slate-400 text-xs sm:text-sm">Profile Views</p>
                            <p class="text-xl sm:text-2xl font-bold text-cyan-400 mt-1">342</p>
                        </div>

                        <!-- Applications Sent -->
                        <div class="p-3 sm:p-4 bg-white/5 rounded-lg border border-white/10">
                            <p class="text-slate-400 text-xs sm:text-sm">Applications Sent</p>
                            <p class="text-xl sm:text-2xl font-bold text-purple-400 mt-1">15</p>
                        </div>

                        <!-- Interviews Scheduled -->
                        <div class="p-3 sm:p-4 bg-white/5 rounded-lg border border-white/10">
                            <p class="text-slate-400 text-xs sm:text-sm">Interviews Scheduled</p>
                            <p class="text-xl sm:text-2xl font-bold text-green-400 mt-1">3</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="mt-6 sm:mt-8 md:mt-12 bg-white/5 border border-white/10 rounded-lg sm:rounded-2xl md:rounded-[2rem] p-4 sm:p-6 md:p-8 backdrop-blur-xl">
                <h3 class="text-lg sm:text-xl font-bold text-white mb-4 sm:mb-6">Recent Activity</h3>
                <div class="space-y-2 sm:space-y-3 max-h-96 overflow-y-auto">
                    <!-- Activity Item 1 -->
                    <div class="flex items-center justify-between py-3 px-2 sm:px-0 border-b border-white/10 last:border-0 gap-2">
                        <div class="min-w-0">
                            <p class="text-white font-medium text-sm sm:text-base truncate">Applied for Senior Frontend Developer</p>
                            <p class="text-slate-400 text-xs sm:text-sm">2 hours ago</p>
                        </div>
                        <span class="px-2 sm:px-3 py-1 bg-cyan-500/20 text-cyan-300 text-xs rounded-full whitespace-nowrap flex-shrink-0">Applied</span>
                    </div>

                    <!-- Activity Item 2 -->
                    <div class="flex items-center justify-between py-3 px-2 sm:px-0 border-b border-white/10 last:border-0 gap-2">
                        <div class="min-w-0">
                            <p class="text-white font-medium text-sm sm:text-base truncate">Saved Backend Developer role at TechCorp</p>
                            <p class="text-slate-400 text-xs sm:text-sm">Yesterday</p>
                        </div>
                        <span class="px-2 sm:px-3 py-1 bg-purple-500/20 text-purple-300 text-xs rounded-full whitespace-nowrap flex-shrink-0">Saved</span>
                    </div>

                    <!-- Activity Item 3 -->
                    <div class="flex items-center justify-between py-3 px-2 sm:px-0 border-b border-white/10 last:border-0 gap-2">
                        <div class="min-w-0">
                            <p class="text-white font-medium text-sm sm:text-base truncate">Interview scheduled - Full Stack Role</p>
                            <p class="text-slate-400 text-xs sm:text-sm">3 days ago</p>
                        </div>
                        <span class="px-2 sm:px-3 py-1 bg-green-500/20 text-green-300 text-xs rounded-full whitespace-nowrap flex-shrink-0">Interview</span>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
