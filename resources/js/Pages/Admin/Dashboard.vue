<script setup>
import { computed, onMounted, onUnmounted, ref, watch, nextTick } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AnalyticsCounter from '@/Components/AnalyticsCounter.vue';
import AdminPageLayout from '@/Layouts/AdminPageLayout.vue';
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

// Navigation menus
const currentRoute = computed(() => route().current());

const menus = [
    { name: 'Dashboard', icon: '📊', route: 'admin.dashboard' },
    { name: 'Analytics', icon: '📈', route: 'admin.analytics' },
    { name: 'Jobs', icon: '💼', route: 'admin.jobs' },
    { name: 'Applicants', icon: '👥', route: 'admin.applicants' },
    { name: 'Settings', icon: '⚙️', route: 'admin.settings' },
];

// Local state for real-time metrics with animating values
const metrics = ref({
    total_applications: props.analytics.total_applications,
    pending_applications: props.analytics.pending_applications,
    hired_count: props.analytics.hired_count,
});

// Animated display values
const displayMetrics = ref({
    total_applications: props.analytics.total_applications,
    pending_applications: props.analytics.pending_applications,
    hired_count: props.analytics.hired_count,
});

// Track recent updates for animation triggers
const recentUpdates = ref({
    total_applications: false,
    pending_applications: false,
    hired_count: false,
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

        // Set animation flags
        recentUpdates.value.total_applications = true;
        recentUpdates.value.pending_applications = true;
        setTimeout(() => {
            recentUpdates.value.total_applications = false;
            recentUpdates.value.pending_applications = false;
        }, 600);

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
            recentUpdates.value.hired_count = true;
            setTimeout(() => {
                recentUpdates.value.hired_count = false;
            }, 600);
        } else if (data.previous_status === 'hired' && data.status !== 'hired') {
            metrics.value.hired_count -= 1;
        }

        if (data.previous_status === 'pending' && data.status !== 'pending') {
            metrics.value.pending_applications -= 1;
            recentUpdates.value.pending_applications = true;
            setTimeout(() => {
                recentUpdates.value.pending_applications = false;
            }, 600);
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

// Animate numbers smoothly
watch(() => metrics.value, (newMetrics) => {
    // Animate display values
    const animationFrames = 30;
    let currentFrame = 0;

    const startValues = {
        total_applications: displayMetrics.value.total_applications,
        pending_applications: displayMetrics.value.pending_applications,
        hired_count: displayMetrics.value.hired_count,
    };

    const animate = () => {
        currentFrame++;
        const progress = currentFrame / animationFrames;

        displayMetrics.value.total_applications = Math.floor(
            startValues.total_applications + 
            (newMetrics.total_applications - startValues.total_applications) * progress
        );
        displayMetrics.value.pending_applications = Math.floor(
            startValues.pending_applications + 
            (newMetrics.pending_applications - startValues.pending_applications) * progress
        );
        displayMetrics.value.hired_count = Math.floor(
            startValues.hired_count + 
            (newMetrics.hired_count - startValues.hired_count) * progress
        );

        if (currentFrame < animationFrames) {
            requestAnimationFrame(animate);
        }
    };

    animate();
}, { deep: true });

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

    <AdminPageLayout title="Dashboard 📊" subtitle="Real-time metrics & insights">
        <!-- Analytics Summary Grid -->
        <div class="mb-12">
            <h3 class="text-sm font-black text-gray-600 uppercase tracking-[0.3em] mb-8 italic">📊 Summary Analytics</h3>
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
                    :value="displayMetrics.total_applications"
                    color="blue"
                    :trend="displayMetrics.total_applications > props.analytics.total_applications ? '+' + (displayMetrics.total_applications - props.analytics.total_applications) : '0'"
                    trendDirection="up"
                    :isUpdating="recentUpdates.total_applications"
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
                    :value="displayMetrics.pending_applications"
                    color="orange"
                    :isUpdating="recentUpdates.pending_applications"
                />
            </div>
        </div>

        <!-- Additional Details Grid -->
        <div class="grid grid-cols-12 gap-10 mb-10">
            
            <!-- Performance Chart Card -->
            <div class="col-span-12 lg:col-span-8 bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-12 italic">📈 Performance Metrics</h4>
                
                <!-- Animated bars -->
                <div class="h-56 flex items-end gap-3 mb-8 border-b border-white/10 pb-4 px-2">
                    <div v-for="(monthValue, idx) in props.analytics.monthly_applications.data" :key="idx" 
                        class="flex-grow bg-gradient-to-t from-cyan-500/60 via-cyan-500/30 to-transparent border border-cyan-400/40 rounded-t-2xl transition-all duration-1000 hover:from-cyan-400/80 hover:via-cyan-400/50 cursor-pointer shadow-[0_-12px_40px_rgba(6,182,212,0.25)] hover:shadow-[0_-16px_60px_rgba(6,182,212,0.4)]"
                        :style="{height: (Math.max(...props.analytics.monthly_applications.data) > 0 ? (monthValue / Math.max(...props.analytics.monthly_applications.data) * 100) : 0) + '%'}"
                        :title="`${props.analytics.monthly_applications.categories[idx]}: ${monthValue} aplikasi`"
                        @mouseenter="$event.target.style.transform = 'scaleY(1.1)'"
                        @mouseleave="$event.target.style.transform = 'scaleY(1)'">
                    </div>
                </div>

                <!-- Stats row -->
                <div class="grid grid-cols-3 gap-8">
                    <div>
                        <p class="text-4xl font-black text-white tracking-tighter">${{ (props.analytics.total_revenue / 1000).toFixed(1) }}k</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-3">Revenue</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-white tracking-tighter">{{ props.analytics.success_rate }}%</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-3">Success Rate</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-cyan-400 tracking-tighter">{{ props.analytics.active_jobs }}</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-3">Active Jobs</p>
                    </div>
                </div>
            </div>

            <!-- Status Breakdown Card -->
            <div class="col-span-12 lg:col-span-4 bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.4em] text-center mb-12 italic">📊 Application Status</h4>
                <div class="space-y-8">
                    <div>
                        <div class="flex justify-between text-[11px] font-black uppercase tracking-widest text-gray-400 mb-4">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-purple-400 rounded-full animate-pulse"></span>
                                Active Jobs
                            </span>
                            <span class="text-purple-400 font-bold">{{ Math.round((props.analytics.active_jobs / (props.analytics.total_applications || 1)) * 100) || 0 }}%</span>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full p-[1px] overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-purple-600 to-purple-400 rounded-full shadow-[0_0_20px_rgba(147,51,234,0.8)]" :style="{width: Math.round((props.analytics.active_jobs / (props.analytics.total_applications || 1)) * 100) + '%' || '0%'}"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[11px] font-black uppercase tracking-widest text-gray-400 mb-4">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-blue-400 rounded-full animate-pulse" style="animation-delay: 0.5s;"></span>
                                Shortlisted
                            </span>
                            <span class="text-blue-400 font-bold">{{ Math.round((props.analytics.shortlisted_applications / (props.analytics.total_applications || 1)) * 100) || 0 }}%</span>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full p-[1px] overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400 rounded-full shadow-[0_0_20px_rgba(59,130,246,0.8)]" :style="{width: Math.round((props.analytics.shortlisted_applications / (props.analytics.total_applications || 1)) * 100) + '%' || '0%'}"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[11px] font-black uppercase tracking-widest text-gray-400 mb-4">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse" style="animation-delay: 1s;"></span>
                                Hired
                            </span>
                            <span class="text-green-400 font-bold">{{ Math.round((displayMetrics.hired_count / (displayMetrics.total_applications || 1)) * 100) || 0 }}%</span>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full p-[1px] overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-green-600 to-green-400 rounded-full shadow-[0_0_20px_rgba(34,197,94,0.8)]" :style="{width: Math.round((displayMetrics.hired_count / (displayMetrics.total_applications || 1)) * 100) + '%' || '0%'}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hired Candidates -->
        <div class="mb-10">
            <h3 class="text-sm font-black text-gray-600 uppercase tracking-[0.3em] mb-8 italic">✅ Successful Hires</h3>
            <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                <p class="text-5xl font-black text-green-400 tracking-tighter">{{ displayMetrics.hired_count }}</p>
                <p class="text-xs text-gray-500 mt-4 font-medium">Candidates successfully hired from applications</p>
            </div>
        </div>
    </AdminPageLayout>
</template>

<style scoped>
/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(6, 182, 212, 0.4);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(6, 182, 212, 0.7);
}
</style>