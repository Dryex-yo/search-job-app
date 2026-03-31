<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
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

// Navigation menus
const currentRoute = computed(() => route().current());

const menus = [
    { name: 'Dashboard', icon: '📊', route: 'admin.dashboard' },
    { name: 'Analytics', icon: '📈', route: 'admin.analytics' },
    { name: 'Jobs', icon: '💼', route: 'admin.jobs' },
    { name: 'Applicants', icon: '👥', route: 'admin.applicants' },
    { name: 'Settings', icon: '⚙️', route: 'admin.settings' },
];

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

    <div class="fixed inset-0 bg-[#080B14] flex items-center justify-center p-4 md:p-8 font-sans overflow-hidden text-white selection:bg-cyan-500/30">
        
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none grain-bg"></div>

        <div class="absolute top-[-10%] left-[-5%] w-[600px] h-[600px] bg-cyan-500/10 blur-[130px] rounded-full animate-pulse-slow"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[700px] h-[700px] bg-blue-600/10 blur-[150px] rounded-full animate-pulse-slow delay-700"></div>

        <div class="w-full max-w-[1440px] h-full max-h-[850px] 
                    bg-white/[0.005] backdrop-blur-[60px] 
                    border border-white/20 
                    rounded-[3.5rem] 
                    shadow-[0_40px_100px_rgba(0,0,0,0.8),inset_0_0_20px_rgba(255,255,255,0.02)] 
                    flex overflow-hidden relative z-10 
                    glowing-border">
            
            <aside class="w-80 border-r border-white/5 p-12 flex flex-col bg-white/[0.002] backdrop-blur-3xl">
                <div class="mb-16">
                    <h1 class="text-3xl font-black text-cyan-400 italic tracking-tighter uppercase">DRYEX<span class="text-white">.</span></h1>
                </div>

                <nav class="flex-grow space-y-3">
                    <Link v-for="menu in menus" :key="menu.name"
                        :href="route(menu.route)"
                        :class="[route().current(menu.route) ? 'bg-white/10 text-white shadow-inner border-white/10' : 'text-gray-500 hover:text-gray-300 border-transparent']"
                        class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl border transition-all duration-500 font-bold text-xs uppercase tracking-widest group"
                    >
                        <span class="opacity-70 group-hover:opacity-100">{{ menu.icon }}</span>
                        {{ menu.name }}
                    </Link>
                </nav>

                <Link :href="route('logout')" method="post" as="button" class="mt-auto text-left px-6 py-4 text-gray-700 hover:text-red-400 font-bold text-xs uppercase tracking-widest transition-colors">Logout</Link>
            </aside>

            <main class="flex-grow flex flex-col overflow-hidden bg-gradient-to-br from-white/[0.005] to-transparent">
                <header class="p-12 pb-6 flex justify-between items-center border-b border-white/5">
                    <h2 class="text-3xl font-medium tracking-tight text-white/90">Dashboard 📊</h2>
                </header>

                <div class="flex-grow p-12 overflow-y-auto custom-scrollbar">
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
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
@keyframes pulse-slow {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}

.animate-pulse-slow {
    animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.delay-700 {
    animation-delay: 0.7s;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(6, 182, 212, 0.3);
    border-radius: 10px;
}

.glowing-border {
    box-shadow: inset 0 0 20px rgba(6, 182, 212, 0.1);
}
</style>