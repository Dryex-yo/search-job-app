<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    dashboardData: {
        type: Object,
        required: true
    }
});

// Computed properties for status colors and icons
const statusConfig = {
    pending: { color: 'orange', icon: '⏳', bg: 'bg-orange-500/10', border: 'border-orange-500/20' },
    shortlisted: { color: 'blue', icon: '⭐', bg: 'bg-blue-500/10', border: 'border-blue-500/20' },
    interview: { color: 'purple', icon: '🎙️', bg: 'bg-purple-500/10', border: 'border-purple-500/20' },
    hired: { color: 'green', icon: '✅', bg: 'bg-green-500/10', border: 'border-green-500/20' },
    rejected: { color: 'red', icon: '❌', bg: 'bg-red-500/10', border: 'border-red-500/20' },
};

const stats = computed(() => props.dashboardData.statistics);
const recentApps = computed(() => props.dashboardData.recentApplications);
const recommendedJobs = computed(() => props.dashboardData.recommendedJobs);
const user = computed(() => props.dashboardData.user);
</script>

<template>
    <Head title="Dashboard" />

    <UserLayout>
        <div class="max-w-7xl mx-auto p-4 sm:p-6 md:p-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">Welcome back, {{ user.name }}! 👋</h1>
                <p class="text-slate-400">Here's your job search dashboard overview</p>
            </div>

            <!-- Main Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- Total Applications -->
                <div class="bg-white/5 border border-white/10 rounded-xl p-6 backdrop-blur-xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">Total Applications</p>
                            <p class="text-3xl font-bold text-cyan-400 mt-2">{{ stats.total_applications }}</p>
                        </div>
                        <span class="text-4xl">📋</span>
                    </div>
                    <p class="text-xs text-slate-500">{{ stats.this_month_applications }} this month</p>
                </div>

                <!-- Shortlisted Count -->
                <div class="bg-white/5 border border-white/10 rounded-xl p-6 backdrop-blur-xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">Shortlisted</p>
                            <p class="text-3xl font-bold text-blue-400 mt-2">{{ stats.shortlisted_applications }}</p>
                        </div>
                        <span class="text-4xl">⭐</span>
                    </div>
                    <div class="text-xs text-slate-500">
                        {{ stats.total_applications > 0 ? Math.round((stats.shortlisted_applications / stats.total_applications) * 100) : 0 }}% conversion
                    </div>
                </div>

                <!-- Interview Scheduled -->
                <div class="bg-white/5 border border-white/10 rounded-xl p-6 backdrop-blur-xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">Interviews</p>
                            <p class="text-3xl font-bold text-purple-400 mt-2">{{ stats.interview_applications }}</p>
                        </div>
                        <span class="text-4xl">🎙️</span>
                    </div>
                    <p class="text-xs text-slate-500">Waiting to schedule</p>
                </div>

                <!-- Successful Hires -->
                <div class="bg-white/5 border border-white/10 rounded-xl p-6 backdrop-blur-xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">Successful Hires</p>
                            <p class="text-3xl font-bold text-green-400 mt-2">{{ stats.hired_applications }}</p>
                        </div>
                        <span class="text-4xl">✅</span>
                    </div>
                    <p class="text-xs text-slate-500">Completed</p>
                </div>
            </div>

            <!-- Secondary Stats & Profile Completion -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <!-- Profile Completion -->
                <div class="bg-white/5 border border-white/10 rounded-xl p-6 backdrop-blur-xl">
                    <p class="text-slate-400 text-sm font-medium mb-4">Profile Completion</p>
                    <p class="text-3xl font-bold text-cyan-400 mb-4">{{ stats.profile_completion }}%</p>
                    <div class="h-2 w-full bg-white/10 rounded-full overflow-hidden">
                        <div 
                            class="h-full bg-gradient-to-r from-cyan-600 to-cyan-400"
                            :style="{ width: stats.profile_completion + '%' }"
                        ></div>
                    </div>
                    <p class="text-xs text-slate-500 mt-3">Complete your profile to improve chances</p>
                </div>

                <!-- Profile Views -->
                <div class="bg-white/5 border border-white/10 rounded-xl p-6 backdrop-blur-xl">
                    <p class="text-slate-400 text-sm font-medium mb-4">Profile Views</p>
                    <p class="text-3xl font-bold text-purple-400 mb-2">{{ stats.profile_views }}</p>
                    <p class="text-sm text-purple-300 font-medium">+{{ stats.profile_views_this_month }} this month</p>
                </div>

                <!-- Pending Applications -->
                <div class="bg-white/5 border border-white/10 rounded-xl p-6 backdrop-blur-xl">
                    <p class="text-slate-400 text-sm font-medium mb-4">Pending Review</p>
                    <p class="text-3xl font-bold text-orange-400 mb-2">{{ stats.pending_applications }}</p>
                    <p class="text-sm text-orange-300 font-medium">Awaiting decision</p>
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <div class="lg:col-span-2 bg-white/5 border border-white/10 rounded-xl p-6 backdrop-blur-xl">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-white">Recent Applications 📝</h3>
                        <p class="text-sm text-slate-400 mt-1">Your latest job submissions</p>
                    </div>

                    <div v-if="recentApps.length > 0" class="space-y-3">
                        <div v-for="app in recentApps" :key="app.id" 
                             :class="[statusConfig[app.status].bg, statusConfig[app.status].border]"
                             class="p-4 border rounded-lg hover:bg-white/[0.08] transition-colors">
                            <div class="flex items-start justify-between mb-2">
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-white font-semibold truncate">{{ app.job_title }}</h4>
                                    <p class="text-sm text-slate-400">{{ app.company_name }} • {{ app.location }}</p>
                                </div>
                                <span class="text-2xl ml-3 flex-shrink-0">{{ statusConfig[app.status].icon }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-slate-400">{{ app.job_type }}</span>
                                    <span class="text-slate-500">•</span>
                                    <span class="text-slate-400">{{ app.salary }}</span>
                                </div>
                                <span class="text-xs text-slate-500">{{ app.created_at }}</span>
                            </div>
                            <div class="mt-3 pt-3 border-t border-white/5">
                                <span class="text-xs font-medium" :class="{
                                    'text-orange-300': app.status === 'pending',
                                    'text-blue-300': app.status === 'shortlisted',
                                    'text-purple-300': app.status === 'interview',
                                    'text-green-300': app.status === 'hired',
                                    'text-red-300': app.status === 'rejected',
                                }">
                                    {{ app.status.charAt(0).toUpperCase() + app.status.slice(1) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <p class="text-slate-400 text-sm">No applications yet. Start exploring jobs!</p>
                    </div>
                </div>

                <!-- Application Status Summary -->
                <div class="bg-white/5 border border-white/10 rounded-xl p-6 backdrop-blur-xl">
                    <h3 class="text-lg font-bold text-white mb-6">Status Breakdown 📊</h3>
                    
                    <div class="space-y-4">
                        <!-- Pending -->
                        <div v-if="stats.pending_applications > 0" class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-300 text-sm">⏳ Pending</span>
                                <span class="text-orange-400 font-bold">{{ stats.pending_applications }}</span>
                            </div>
                            <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-orange-500" :style="{ width: (stats.pending_applications / stats.total_applications * 100) + '%' }"></div>
                            </div>
                        </div>

                        <!-- Shortlisted -->
                        <div v-if="stats.shortlisted_applications > 0" class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-300 text-sm">⭐ Shortlisted</span>
                                <span class="text-blue-400 font-bold">{{ stats.shortlisted_applications }}</span>
                            </div>
                            <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500" :style="{ width: (stats.shortlisted_applications / stats.total_applications * 100) + '%' }"></div>
                            </div>
                        </div>

                        <!-- Interview -->
                        <div v-if="stats.interview_applications > 0" class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-300 text-sm">🎙️ Interview</span>
                                <span class="text-purple-400 font-bold">{{ stats.interview_applications }}</span>
                            </div>
                            <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-purple-500" :style="{ width: (stats.interview_applications / stats.total_applications * 100) + '%' }"></div>
                            </div>
                        </div>

                        <!-- Hired -->
                        <div v-if="stats.hired_applications > 0" class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-300 text-sm">✅ Hired</span>
                                <span class="text-green-400 font-bold">{{ stats.hired_applications }}</span>
                            </div>
                            <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500" :style="{ width: (stats.hired_applications / stats.total_applications * 100) + '%' }"></div>
                            </div>
                        </div>

                        <!-- Rejected -->
                        <div v-if="stats.rejected_applications > 0" class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-300 text-sm">❌ Rejected</span>
                                <span class="text-red-400 font-bold">{{ stats.rejected_applications }}</span>
                            </div>
                            <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-red-500" :style="{ width: (stats.rejected_applications / stats.total_applications * 100) + '%' }"></div>
                            </div>
                        </div>

                        <div v-if="stats.total_applications === 0" class="text-center py-6">
                            <p class="text-slate-400 text-sm">No applications yet</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommended Jobs Section -->
            <div v-if="recommendedJobs.length > 0" class="mb-8">
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-white">Recommended Jobs 💼</h3>
                    <p class="text-slate-400 mt-1">Based on your activity</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="job in recommendedJobs" :key="job.id"
                         class="bg-white/5 border border-white/10 rounded-xl p-6 hover:bg-white/[0.08] hover:border-white/20 transition-all cursor-pointer group">
                        <div class="mb-4">
                            <h4 class="text-white font-bold group-hover:text-cyan-400 transition-colors">{{ job.title }}</h4>
                            <p class="text-sm text-slate-400">{{ job.company_name }}</p>
                        </div>

                        <div class="space-y-2 text-sm mb-4">
                            <div class="flex items-center text-slate-400">
                                <span class="mr-2">📍</span> {{ job.location }}
                            </div>
                            <div class="flex items-center text-slate-400">
                                <span class="mr-2">💰</span> {{ job.salary }}
                            </div>
                            <div class="flex items-center text-slate-400">
                                <span class="mr-2">🏢</span> {{ job.type }}
                            </div>
                        </div>

                        <div class="pt-4 border-t border-white/10 flex items-center justify-between">
                            <span class="text-xs text-slate-500">{{ job.applications_count }} applicants</span>
                            <button class="px-3 py-1 bg-cyan-500/20 text-cyan-300 text-xs rounded hover:bg-cyan-500/30 transition-colors">
                                Apply Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="/jobs" class="bg-gradient-to-br from-cyan-500/10 to-cyan-500/5 border border-cyan-500/30 rounded-xl p-6 hover:from-cyan-500/20 hover:to-cyan-500/10 transition-all">
                    <p class="text-2xl mb-2">🔍</p>
                    <h4 class="text-white font-semibold">Browse Jobs</h4>
                    <p class="text-sm text-slate-400 mt-1">Explore more opportunities</p>
                </a>

                <a href="/profile" class="bg-gradient-to-br from-purple-500/10 to-purple-500/5 border border-purple-500/30 rounded-xl p-6 hover:from-purple-500/20 hover:to-purple-500/10 transition-all">
                    <p class="text-2xl mb-2">👤</p>
                    <h4 class="text-white font-semibold">Edit Profile</h4>
                    <p class="text-sm text-slate-400 mt-1">Improve your chances</p>
                </a>

                <div class="bg-gradient-to-br from-green-500/10 to-green-500/5 border border-green-500/30 rounded-xl p-6">
                    <p class="text-2xl mb-2">📊</p>
                    <h4 class="text-white font-semibold">View Stats</h4>
                    <p class="text-sm text-slate-400 mt-1">{{ stats.total_applications }} total applications</p>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
