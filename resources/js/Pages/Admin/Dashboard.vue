<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AnalyticsCounter from '@/Components/AnalyticsCounter.vue';

defineProps({
    analytics: {
        type: Object,
        required: true
    }
});

// Mengambil info route saat ini untuk menentukan menu yang aktif secara otomatis
const currentRoute = computed(() => route().current());

const menus = [
    { name: 'Dashboard', icon: '📊', route: 'admin.dashboard' },
    { name: 'Analytics', icon: '📈', route: 'admin.analytics' }, 
    { name: 'Jobs', icon: '💼', route: 'admin.jobs' },
    { name: 'Applicants', icon: '👥', route: 'admin.applicants' },
    { name: 'Settings', icon: '⚙️', route: 'admin.settings' },
];
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
                <header class="p-12 pb-6 flex justify-between items-center">
                    <div>
                        <h2 class="text-3xl font-medium tracking-tight text-white/90">Hi, Admin Dryex! 👋</h2>
                        <p class="text-gray-600 text-sm mt-1 font-medium italic">Workspace is optimized and running smooth.</p>
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
                    <!-- Analytics Summary Grid -->
                    <div class="mb-12">
                        <h3 class="text-sm font-black text-gray-600 uppercase tracking-[0.3em] mb-8 italic">📊 Summary Analytics</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <AnalyticsCounter 
                                icon="💼"
                                label="Total Jobs Posted"
                                :value="analytics.total_jobs"
                                color="cyan"
                            />
                            <AnalyticsCounter 
                                icon="📋"
                                label="Total Applications"
                                :value="analytics.total_applications"
                                color="blue"
                            />
                            <AnalyticsCounter 
                                icon="👥"
                                label="Total Users"
                                :value="analytics.total_users"
                                color="green"
                            />
                            <AnalyticsCounter 
                                icon="✨"
                                label="Active Jobs"
                                :value="analytics.active_jobs"
                                color="purple"
                            />
                            <AnalyticsCounter 
                                icon="⏳"
                                label="Pending Applications"
                                :value="analytics.pending_applications"
                                color="orange"
                            />
                            <AnalyticsCounter 
                                icon="✅"
                                label="Hired Candidates"
                                :value="analytics.hired_count"
                                color="pink"
                            />
                        </div>
                    </div>

                    <!-- Additional Details Grid -->
                    <div class="grid grid-cols-12 gap-10">
                        
                        <div class="col-span-12 lg:col-span-8 bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 relative shadow-inner glass-grain">
                            <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-12 italic">Performance Metrics</h4>
                            
                            <div class="h-56 flex items-end gap-3.5 mb-14 border-b border-white/5 pb-5 px-3">
                                <div v-for="n in 12" :key="n" 
                                    class="flex-grow bg-gradient-to-t from-cyan-500/40 via-cyan-500/10 to-transparent border-t border-cyan-400/30 rounded-t-xl transition-all duration-1000 hover:h-full cursor-pointer shadow-[0_-8px_30px_rgba(6,182,212,0.15)]"
                                    :style="{height: Math.floor(Math.random() * 60 + 30) + '%'}">
                                </div>
                            </div>

                            <div class="flex gap-16 justify-between">
                                <div><p class="text-5xl font-extrabold text-white tracking-tighter">$14.5k</p><p class="text-[10px] font-black text-gray-700 uppercase tracking-widest mt-2">Revenue</p></div>
                                <div><p class="text-5xl font-extrabold text-white tracking-tighter">92%</p><p class="text-[10px] font-black text-gray-700 uppercase tracking-widest mt-2">Success Rate</p></div>
                                <div><p class="text-5xl font-extrabold text-white tracking-tighter">{{ analytics.active_jobs }}</p><p class="text-[10px] font-black text-gray-600 uppercase tracking-widest mt-2 font-black italic">Active Jobs</p></div>
                            </div>
                        </div>

                        <div class="col-span-12 lg:col-span-4 bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 flex flex-col justify-between shadow-inner">
                            <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.4em] text-center italic">Application Status Breakdown</h4>
                            <div class="space-y-10 py-6">
                                <div>
                                    <div class="flex justify-between text-[11px] font-black uppercase tracking-widest text-gray-500 mb-4">
                                        <span>Pending</span>
                                        <span class="text-orange-400">{{ Math.round((analytics.pending_applications / analytics.total_applications) * 100) || 0 }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-white/5 rounded-full p-[1px]">
                                        <div class="h-full bg-gradient-to-r from-orange-600 to-orange-400 rounded-full shadow-[0_0_20px_rgba(249,115,22,0.7)] transition-all duration-1000" :style="{width: Math.round((analytics.pending_applications / analytics.total_applications) * 100) + '%' || '0%'}"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[11px] font-black uppercase tracking-widest text-gray-500 mb-4">
                                        <span>Shortlisted</span>
                                        <span class="text-blue-400">{{ Math.round((analytics.shortlisted_applications / analytics.total_applications) * 100) || 0 }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-white/5 rounded-full p-[1px]">
                                        <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400 rounded-full shadow-[0_0_20px_rgba(59,130,246,0.7)] transition-all duration-1000" :style="{width: Math.round((analytics.shortlisted_applications / analytics.total_applications) * 100) + '%' || '0%'}"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[11px] font-black uppercase tracking-widest text-gray-500 mb-4">
                                        <span>Hired</span>
                                        <span class="text-green-400">{{ Math.round((analytics.hired_count / analytics.total_applications) * 100) || 0 }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-white/5 rounded-full p-[1px]">
                                        <div class="h-full bg-gradient-to-r from-green-600 to-green-400 rounded-full shadow-[0_0_20px_rgba(34,197,94,0.7)] transition-all duration-1000" :style="{width: Math.round((analytics.hired_count / analytics.total_applications) * 100) + '%' || '0%'}"></div>
                                    </div>
                                </div>
                            </div>
                            <button class="w-full py-4.5 bg-white text-black rounded-3xl font-black text-[10px] uppercase tracking-widest hover:bg-cyan-400 transition-colors">View Details</button>
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