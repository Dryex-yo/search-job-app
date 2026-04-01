<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AnalyticsCounter from '@/Components/AnalyticsCounter.vue';
import ChartWeeklyApplicants from '@/Components/ChartWeeklyApplicants.vue';
import ChartStatusDistribution from '@/Components/ChartStatusDistribution.vue';
import AdminPageLayout from '@/Layouts/AdminPageLayout.vue';

const props = defineProps({
    analytics: {
        type: Object,
        required: true
    },
    chartData: {
        type: Object,
        required: true
    }
});

// Calculate percentages for analytics
const totalApps = computed(() => props.analytics.total_applications || 1);
const pendingPercent = computed(() => Math.round((props.analytics.pending_applications / totalApps.value) * 100));
const shortlistedPercent = computed(() => Math.round((props.analytics.shortlisted_applications / totalApps.value) * 100));
const rejectedPercent = computed(() => Math.round((props.analytics.rejected_applications / totalApps.value) * 100));
const hiredPercent = computed(() => Math.round((props.analytics.hired_count / totalApps.value) * 100));
</script>

<template>
    <Head title="Dryex Admin - Analytics" />

    <AdminPageLayout title="Analytics Report 📈" subtitle="Comprehensive platform performance metrics">
        <!-- Key Metrics Overview -->
        <div class="mb-12">
            <h3 class="text-sm font-black text-gray-600 uppercase tracking-[0.3em] mb-8 italic">📊 Key Metrics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <AnalyticsCounter 
                    icon="💼"
                    label="Active Job Listings"
                    :value="analytics.active_jobs"
                    color="cyan"
                />
                <AnalyticsCounter 
                    icon="👥"
                    label="Total Platform Users"
                    :value="analytics.total_users"
                    color="green"
                />
                <AnalyticsCounter 
                    icon="📧"
                    label="Total Applications"
                    :value="analytics.total_applications"
                    color="blue"
                />
                <AnalyticsCounter 
                    icon="✅"
                    label="Successful Hires"
                    :value="analytics.hired_count"
                    color="pink"
                />
            </div>
        </div>

        <!-- Data Visualization Charts -->
        <div class="mb-12">
            <h3 class="text-sm font-black text-gray-600 uppercase tracking-[0.3em] mb-8 italic">📊 Charts & Visualizations</h3>
            <div class="grid grid-cols-12 gap-10 mb-10">
                <!-- Weekly Applicants Line Chart -->
                <div class="col-span-12 lg:col-span-7">
                    <ChartWeeklyApplicants :weeklyData="chartData.weeklyApplicants" />
                </div>
                
                <!-- Status Distribution Pie Chart -->
                <div class="col-span-12 lg:col-span-5">
                    <ChartStatusDistribution :statusData="chartData.statusDistribution" />
                </div>
            </div>
        </div>

        <!-- Application Funnel Analysis -->
        <div class="grid grid-cols-12 gap-10 mb-10">
            <div class="col-span-12 lg:col-span-7 bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-12 italic">Application Processing Funnel</h4>
                
                <div class="space-y-8">
                    <!-- Pending -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">⏳</span>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-500">Pending Review</p>
                                    <p class="text-xl font-black text-white">{{ analytics.pending_applications }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-black text-orange-400">{{ pendingPercent }}%</span>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full p-[1px]">
                            <div class="h-full bg-gradient-to-r from-orange-600 to-orange-400 rounded-full shadow-[0_0_20px_rgba(249,115,22,0.7)]" :style="{width: pendingPercent + '%'}"></div>
                        </div>
                    </div>

                    <!-- Shortlisted -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">⭐</span>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-500">Shortlisted</p>
                                    <p class="text-xl font-black text-white">{{ analytics.shortlisted_applications }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-black text-blue-400">{{ shortlistedPercent }}%</span>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full p-[1px]">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400 rounded-full shadow-[0_0_20px_rgba(59,130,246,0.7)]" :style="{width: shortlistedPercent + '%'}"></div>
                        </div>
                    </div>

                    <!-- Hired -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🎉</span>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-500">Successfully Hired</p>
                                    <p class="text-xl font-black text-white">{{ analytics.hired_count }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-black text-green-400">{{ hiredPercent }}%</span>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full p-[1px]">
                            <div class="h-full bg-gradient-to-r from-green-600 to-green-400 rounded-full shadow-[0_0_20px_rgba(34,197,94,0.7)]" :style="{width: hiredPercent + '%'}"></div>
                        </div>
                    </div>

                    <!-- Rejected -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">❌</span>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-500">Not Selected</p>
                                    <p class="text-xl font-black text-white">{{ analytics.rejected_applications }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-black text-red-400">{{ rejectedPercent }}%</span>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full p-[1px]">
                            <div class="h-full bg-gradient-to-r from-red-600 to-red-400 rounded-full shadow-[0_0_20px_rgba(239,68,68,0.7)]" :style="{width: rejectedPercent + '%'}"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="col-span-12 lg:col-span-5 space-y-6">
                <!-- Conversion Rate -->
                <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-10 shadow-inner">
                    <p class="text-[10px] font-black text-gray-700 uppercase tracking-[0.3em] mb-6 italic">Hire Conversion Rate</p>
                    <div class="mb-6">
                        <p class="text-5xl font-black text-cyan-400 tracking-tighter">{{ hiredPercent }}<span class="text-2xl">%</span></p>
                    </div>
                    <div class="h-1.5 w-full bg-white/5 rounded-full p-[1px]">
                        <div class="h-full bg-gradient-to-r from-cyan-600 to-cyan-400 rounded-full shadow-[0_0_20px_rgba(6,182,212,0.7)]" :style="{width: hiredPercent + '%'}"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-4 font-medium">{{ analytics.hired_count }} out of {{ analytics.total_applications }} applications resulted in hire</p>
                </div>

                <!-- Job Stats -->
                <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-10 shadow-inner">
                    <p class="text-[10px] font-black text-gray-700 uppercase tracking-[0.3em] mb-6 italic">Job Listings</p>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">Total Jobs</span>
                            <span class="text-2xl font-black text-white">{{ analytics.total_jobs }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">Currently Active</span>
                            <span class="text-2xl font-black text-cyan-400">{{ analytics.active_jobs }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">Closed/Expired</span>
                            <span class="text-2xl font-black text-gray-400">{{ analytics.total_jobs - analytics.active_jobs }}</span>
                        </div>
                    </div>
                </div>

                <!-- User Engagement -->
                <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-10 shadow-inner">
                    <p class="text-[10px] font-black text-gray-700 uppercase tracking-[0.3em] mb-6 italic">Platform Engagement</p>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">Total Users</span>
                            <span class="text-2xl font-black text-green-400">{{ analytics.total_users }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">Avg. Apps/User</span>
                            <span class="text-2xl font-black text-blue-400">{{ analytics.total_users > 0 ? Math.round(analytics.total_applications / analytics.total_users * 10) / 10 : 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Indicators -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="bg-gradient-to-br from-cyan-500/10 to-cyan-500/5 border border-cyan-500/30 rounded-[2.5rem] p-8 text-center">
                <p class="text-5xl mb-3">📊</p>
                <p class="text-sm font-black text-gray-600 uppercase tracking-widest mb-2">Platform Health</p>
                <p class="text-3xl font-black text-cyan-400">Excellent</p>
                <p class="text-xs text-gray-500 mt-3">System running smoothly with optimal performance</p>
            </div>

            <div class="bg-gradient-to-br from-green-500/10 to-green-500/5 border border-green-500/30 rounded-[2.5rem] p-8 text-center">
                <p class="text-5xl mb-3">🚀</p>
                <p class="text-sm font-black text-gray-600 uppercase tracking-widest mb-2">Growth Trend</p>
                <p class="text-3xl font-black text-green-400">Positive</p>
                <p class="text-xs text-gray-500 mt-3">Consistent increase in applications and user engagement</p>
            </div>

            <div class="bg-gradient-to-br from-blue-500/10 to-blue-500/5 border border-blue-500/30 rounded-[2.5rem] p-8 text-center">
                <p class="text-5xl mb-3">💡</p>
                <p class="text-sm font-black text-gray-600 uppercase tracking-widest mb-2">Insights</p>
                <p class="text-3xl font-black text-blue-400">{{ hiredPercent }}%</p>
                <p class="text-xs text-gray-500 mt-3">Success rate from applications to hiring</p>
            </div>
        </div>
    </AdminPageLayout>
</template>
