<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AnalyticsCounter from '@/Components/AnalyticsCounter.vue';
import { useRealtimeEvents } from '@/Composables/useRealtimeEvents';
import { useSoundNotification } from '@/Composables/useSoundNotification';
import { useNotification } from '@/Composables/useNotification';

const props = defineProps({
    analytics: {
        type: Object,
        required: true
    }
});

// Composables
const { listenToApplications, stopListeningToApplications } = useRealtimeEvents();
const { notify: playSound } = useSoundNotification();
const { success, info } = useNotification();

// Local state for real-time metrics
const metrics = ref({
    total_applications: props.analytics.total_applications,
    pending_applications: props.analytics.pending_applications,
    hired_count: props.analytics.hired_count,
});

/**
 * Handle real-time application events
 */
const handleApplicationEvent = (event) => {
    const { type, data } = event;

    if (type === 'application.submitted') {
        // Update total applications counter
        metrics.value.total_applications += 1;
        metrics.value.pending_applications += 1;

        // Play sound and show notification
        playSound();
        success(
            '📨 New Application!',
            `${data.user.name} applied for "${data.job.title}"`,
            5000
        );

        console.log('✨ New application received:', data);
    } else if (type === 'application.status-changed') {
        // Update metrics based on status change
        if (data.previous_status !== 'hired' && data.status === 'hired') {
            metrics.value.hired_count += 1;
        } else if (data.previous_status === 'hired' && data.status !== 'hired') {
            metrics.value.hired_count -= 1;
        }

        if (data.previous_status === 'pending' && data.status !== 'pending') {
            metrics.value.pending_applications -= 1;
        } else if (data.previous_status !== 'pending' && data.status === 'pending') {
            metrics.value.pending_applications += 1;
        }

        // Play sound
        playSound();
        info(
            '📊 Status Updated',
            `${data.user.name}'s application is now "${data.status}"`,
            5000
        );

        console.log('✨ Application status changed:', data);
    }
};

// Initialize real-time listeners
onMounted(() => {
    console.log('🚀 Dashboard mounted - initializing real-time events');
    listenToApplications(handleApplicationEvent);
});

// Cleanup on unmount
onUnmounted(() => {
    console.log('🧹 Dashboard unmounted - stopping listeners');
    stopListeningToApplications();
});
</script>

<template>
    <Head title="Dryex Admin - Dashboard" />

    <AdminLayout>
        <template #title>Dashboard</template>

        <!-- Analytics Summary Grid -->
        <div class="mb-8">
            <h3 class="text-sm font-black text-gray-400 uppercase tracking-[0.3em] mb-6 italic">📊 Summary Analytics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <AnalyticsCounter 
                    icon="💼"
                    label="Total Jobs Posted"
                    :value="props.analytics.total_jobs"
                    color="cyan"
                />
                <AnalyticsCounter 
                    icon="📋"
                    label="Total Applications"
                    :value="metrics.total_applications"
                    color="blue"
                    :trend="metrics.total_applications > props.analytics.total_applications ? '+' + (metrics.total_applications - props.analytics.total_applications) : '0'"
                    trendDirection="up"
                />
                <AnalyticsCounter 
                    icon="✨"
                    label="Active Jobs"
                    :value="props.analytics.active_jobs"
                    color="purple"
                />
                <AnalyticsCounter 
                    icon="⏳"
                    label="Pending Applications"
                    :value="metrics.pending_applications"
                    color="orange"
                />
                <AnalyticsCounter 
                    icon="✅"
                    label="Hired Candidates"
                    :value="metrics.hired_count"
                    color="pink"
                    :trend="metrics.hired_count > props.analytics.hired_count ? '+' + (metrics.hired_count - props.analytics.hired_count) : '0'"
                    trendDirection="up"
                />
            </div>
        </div>

        <!-- Additional Details Grid -->
        <div class="grid grid-cols-12 gap-6">
            
            <div class="col-span-12 lg:col-span-8 bg-white/[0.01] border border-white/10 rounded-[2rem] p-8 relative shadow-inner">
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.5em] mb-8 italic">Performance Metrics</h4>
                
                <div class="h-48 flex items-end gap-2 mb-8 border-b border-white/5 pb-4 px-2">
                    <div v-for="n in 12" :key="n" 
                        class="flex-grow bg-gradient-to-t from-cyan-500/40 via-cyan-500/10 to-transparent border-t border-cyan-400/30 rounded-t-xl transition-all duration-1000 hover:h-full cursor-pointer shadow-[0_-8px_30px_rgba(6,182,212,0.15)]"
                        :style="{height: Math.floor(Math.random() * 60 + 30) + '%'}">
                    </div>
                </div>

                <div class="flex gap-8 justify-between">
                    <div>
                        <p class="text-4xl font-extrabold text-white tracking-tighter">$14.5k</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">Revenue</p>
                    </div>
                    <div>
                        <p class="text-4xl font-extrabold text-white tracking-tighter">92%</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">Success Rate</p>
                    </div>
                    <div>
                        <p class="text-4xl font-extrabold text-white tracking-tighter">{{ props.analytics.active_jobs }}</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">Active Jobs</p>
                    </div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-4 bg-white/[0.01] border border-white/10 rounded-[2rem] p-8 flex flex-col justify-between shadow-inner">
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.4em] text-center italic">Application Status Breakdown</h4>
                <div class="space-y-6 py-4">
                    <div>
                        <div class="flex justify-between text-[11px] font-black uppercase tracking-widest text-gray-400 mb-3">
                            <span>Active Jobs</span>
                            <span class="text-purple-400">{{ Math.round((props.analytics.active_jobs / (props.analytics.total_applications || 1)) * 100) || 0 }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-white/5 rounded-full p-[1px]">
                            <div class="h-full bg-gradient-to-r from-purple-600 to-purple-400 rounded-full shadow-[0_0_20px_rgba(147,51,234,0.7)] transition-all duration-1000" :style="{width: Math.round((props.analytics.active_jobs / (props.analytics.total_applications || 1)) * 100) + '%' || '0%'}"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[11px] font-black uppercase tracking-widest text-gray-400 mb-3">
                            <span>Shortlisted</span>
                            <span class="text-blue-400">50%</span>
                        </div>
                        <div class="h-1.5 w-full bg-white/5 rounded-full p-[1px]">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400 rounded-full shadow-[0_0_20px_rgba(59,130,246,0.7)] transition-all duration-1000" style="width: 50%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[11px] font-black uppercase tracking-widest text-gray-400 mb-3">
                            <span>Hired</span>
                            <span class="text-green-400">{{ Math.round((metrics.hired_count / (metrics.total_applications || 1)) * 100) || 0 }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-white/5 rounded-full p-[1px]">
                            <div class="h-full bg-gradient-to-r from-green-600 to-green-400 rounded-full shadow-[0_0_20px_rgba(34,197,94,0.7)] transition-all duration-1000" :style="{width: Math.round((metrics.hired_count / (metrics.total_applications || 1)) * 100) + '%' || '0%'}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(6, 182, 212, 0.3);
    border-radius: 10px;
}
</style>