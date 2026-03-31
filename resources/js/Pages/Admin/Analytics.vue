<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AnalyticsCounter from '@/Components/AnalyticsCounter.vue';
import ChartWeeklyApplicants from '@/Components/ChartWeeklyApplicants.vue';
import ChartStatusDistribution from '@/Components/ChartStatusDistribution.vue';

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

const currentRoute = computed(() => route().current());

const menus = [
    { name: 'Dashboard', icon: '📊', route: 'admin.dashboard' },
    { name: 'Analytics', icon: '📈', route: 'admin.analytics' }, 
    { name: 'Jobs', icon: '💼', route: 'admin.jobs' },
    { name: 'Applicants', icon: '👥', route: 'admin.applicants' },
    { name: 'Settings', icon: '⚙️', route: 'admin.settings' },
];

// Calculate percentages for analytics
const totalApps = computed(() => props.analytics.total_applications || 1);
const pendingPercent = computed(() => Math.round((props.analytics.pending_applications / totalApps.value) * 100));
const shortlistedPercent = computed(() => Math.round((props.analytics.shortlisted_applications / totalApps.value) * 100));
const rejectedPercent = computed(() => Math.round((props.analytics.rejected_applications / totalApps.value) * 100));
const hiredPercent = computed(() => Math.round((props.analytics.hired_count / totalApps.value) * 100));
</script>

<template>
    <Head title="Dryex Admin - Analytics" />

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
                <header class="p-12 pb-6 flex justify-between items-center">
                    <div>
                        <h2 class="text-3xl font-medium tracking-tight text-white/90">Analytics Report 📈</h2>
                        <p class="text-gray-600 text-sm mt-1 font-medium italic">Comprehensive platform performance metrics</p>
                    </div>

                    <div class="flex items-center gap-5 bg-white/[0.02] border border-white/10 p-2.5 pr-8 rounded-3xl shadow-inner">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center font-bold text-white shadow-lg shadow-cyan-500/20">D</div>
                        <div class="text-left leading-tight">
                            <p class="text-xs font-black">Dery Supriyadi</p>
                            <p class="text-[9px] text-cyan-400 uppercase tracking-[0.2em] font-black italic">Administrator</p>
                        </div>
                    </div>
                </header>

                <div class="flex-grow p-12 pt-6 overflow-y-auto custom-scrollbar">
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
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.grain-bg {
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
}

.glowing-border {
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.glowing-border::before {
    content: '';
    position: absolute;
    top: -2px; left: -2px; right: -2px; bottom: -2px;
    border-radius: 3.7rem;
    padding: 1px;
    background: linear-gradient(135deg, rgba(255,255,255,0.3) 0%, transparent 40%, transparent 60%, rgba(255,255,255,0.1) 100%);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: destination-out;
    mask-composite: exclude;
    pointer-events: none;
    z-index: -1;
}

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.05); border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(6, 182, 212, 0.2); }

@keyframes pulse-slow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}
.animate-pulse-slow {
    animation: pulse-slow 5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.glass-grain {
    position: relative;
}
.glass-grain::after {
    content: "";
    position: absolute;
    inset: 0;
    opacity: 0.05;
    pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
}
</style>
