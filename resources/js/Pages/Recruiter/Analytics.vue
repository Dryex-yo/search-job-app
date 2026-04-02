<script setup>
import { Head } from '@inertiajs/vue3';
import RecruiterPageLayout from '@/Layouts/RecruiterPageLayout.vue';
import AnalyticsCounter from '@/Components/AnalyticsCounter.vue';

const props = defineProps({
    analytics: {
        type: Object,
        required: true
    },
    chartData: {
        type: Object,
        default: () => ({})
    },
    topPerformingJobs: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <Head title="Dryex Recruiter - Analytics" />

    <RecruiterPageLayout title="Analytics" subtitle="Recruitment performance and insights">
        <!-- Summary Stats -->
        <div class="mb-12">
            <h3 class="text-sm font-black text-gray-700 dark:text-gray-400 uppercase tracking-[0.3em] mb-8 italic">📊 Key Metrics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <AnalyticsCounter 
                    icon="📋"
                    label="Total Applications"
                    :value="props.analytics.total_applications"
                    color="blue"
                />
                <AnalyticsCounter 
                    icon="⏳"
                    label="Pending Applications"
                    :value="props.analytics.pending_applications"
                    color="orange"
                />
                <AnalyticsCounter 
                    icon="✅"
                    label="Hired"
                    :value="props.analytics.hired_count"
                    color="green"
                />
                <AnalyticsCounter 
                    icon="🎯"
                    label="Success Rate"
                    :value="props.analytics.success_rate"
                    unit="%"
                    color="purple"
                />
            </div>
        </div>

        <!-- Additional Stats Grid -->
        <div class="mb-12">
            <h3 class="text-sm font-black text-gray-700 dark:text-gray-400 uppercase tracking-[0.3em] mb-6 italic">📈 Status Distribution</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="p-4 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase mb-2">Pending</p>
                    <p class="text-2xl font-black text-yellow-500">{{ props.analytics.pending_applications }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase mb-2">Shortlisted</p>
                    <p class="text-2xl font-black text-blue-500">{{ props.analytics.shortlisted_applications }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase mb-2">Interview</p>
                    <p class="text-2xl font-black text-cyan-500">0</p>
                </div>
                <div class="p-4 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase mb-2">Hired</p>
                    <p class="text-2xl font-black text-green-500">{{ props.analytics.hired_count }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase mb-2">Rejected</p>
                    <p class="text-2xl font-black text-red-500">{{ props.analytics.rejected_applications }}</p>
                </div>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="mb-12">
            <h3 class="text-sm font-black text-gray-700 dark:text-gray-400 uppercase tracking-[0.3em] mb-6 italic">📅 Monthly Applications</h3>
            <div class="p-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl">
                <div class="overflow-x-auto">
                    <div class="flex gap-4 min-w-full pb-4">
                        <div v-for="(data, idx) in props.chartData.monthlyApplications" :key="idx" class="flex-shrink-0 flex flex-col items-center">
                            <div class="h-32 bg-gradient-to-t from-blue-500/30 to-blue-500/10 border border-blue-500/50 rounded-lg w-8 flex items-end justify-center relative" :style="{ height: data.count * 10 + 32 + 'px' }">
                                <span class="text-xs font-bold text-blue-400 mb-1">{{ data.count }}</span>
                            </div>
                            <span class="text-xs text-gray-500 mt-2 whitespace-nowrap">{{ data.month }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="p-6 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-500/10 dark:to-purple-500/10 border border-blue-200 dark:border-blue-500/30 rounded-2xl">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Recruitment Insights</h4>
            <p class="text-gray-700 dark:text-gray-300">
                Your team has reviewed <strong>{{ props.analytics.total_applications }}</strong> applications with a success rate of <strong>{{ props.analytics.success_rate }}%</strong>. 
                Continue reviewing pending applications to improve your hiring pipeline.
            </p>
        </div>
    </RecruiterPageLayout>
</template>
