<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    analytics: {
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
</script>

<template>
    <Head title="Dryex Admin - Jobs Management" />

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
                        <h2 class="text-3xl font-medium tracking-tight text-white/90">Jobs Management 💼</h2>
                        <p class="text-gray-600 text-sm mt-1 font-medium italic">Manage and monitor all job listings</p>
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
                    <!-- Quick Actions -->
                    <div class="mb-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <button class="bg-gradient-to-br from-cyan-500/20 to-cyan-500/5 border border-cyan-500/30 hover:border-cyan-500/60 rounded-2xl p-6 text-left transition-all hover:shadow-lg hover:shadow-cyan-500/20">
                            <p class="text-3xl mb-2">➕</p>
                            <p class="font-bold text-white mb-1">Create New Job</p>
                            <p class="text-xs text-gray-500">Add a new job listing to the platform</p>
                        </button>

                        <button class="bg-gradient-to-br from-blue-500/20 to-blue-500/5 border border-blue-500/30 hover:border-blue-500/60 rounded-2xl p-6 text-left transition-all hover:shadow-lg hover:shadow-blue-500/20">
                            <p class="text-3xl mb-2">📋</p>
                            <p class="font-bold text-white mb-1">Active Listings</p>
                            <p class="text-lg font-black text-blue-400">{{ analytics.active_jobs }}</p>
                        </button>

                        <button class="bg-gradient-to-br from-green-500/20 to-green-500/5 border border-green-500/30 hover:border-green-500/60 rounded-2xl p-6 text-left transition-all hover:shadow-lg hover:shadow-green-500/20">
                            <p class="text-3xl mb-2">🎯</p>
                            <p class="font-bold text-white mb-1">Total Applications</p>
                            <p class="text-lg font-black text-green-400">{{ analytics.total_applications }}</p>
                        </button>
                    </div>

                    <!-- Job Stats Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                            <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-8 italic">Job Listings Overview</h4>
                            
                            <div class="space-y-8">
                                <div>
                                    <div class="flex justify-between items-center mb-3">
                                        <p class="text-sm font-bold text-gray-400">Total Jobs Created</p>
                                        <p class="text-2xl font-black text-white">{{ analytics.total_jobs }}</p>
                                    </div>
                                    <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-cyan-600 to-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.7)]" style="width: 100%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between items-center mb-3">
                                        <p class="text-sm font-bold text-gray-400">Currently Active</p>
                                        <p class="text-2xl font-black text-cyan-400">{{ analytics.active_jobs }}</p>
                                    </div>
                                    <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-cyan-600 to-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.7)]" :style="{width: Math.round((analytics.active_jobs / analytics.total_jobs) * 100) + '%' || '0%'}"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between items-center mb-3">
                                        <p class="text-sm font-bold text-gray-400">Closed/Archived</p>
                                        <p class="text-2xl font-black text-gray-500">{{ analytics.total_jobs - analytics.active_jobs }}</p>
                                    </div>
                                    <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-gray-600 to-gray-500 shadow-[0_0_20px_rgba(107,114,128,0.7)]" :style="{width: Math.round(((analytics.total_jobs - analytics.active_jobs) / analytics.total_jobs) * 100) + '%' || '0%'}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                            <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-8 italic">Application Distribution</h4>
                            
                            <div class="space-y-8">
                                <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">⏳</span>
                                        <span class="font-bold text-gray-300">Pending</span>
                                    </div>
                                    <span class="text-xl font-black text-orange-400">{{ analytics.pending_applications }}</span>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">⭐</span>
                                        <span class="font-bold text-gray-300">Shortlisted</span>
                                    </div>
                                    <span class="text-xl font-black text-blue-400">{{ analytics.shortlisted_applications }}</span>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">✅</span>
                                        <span class="font-bold text-gray-300">Hired</span>
                                    </div>
                                    <span class="text-xl font-black text-green-400">{{ analytics.hired_count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Jobs Table Mock -->
                    <div class="mt-10 bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                        <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-8 italic">Popular Job Categories</h4>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 hover:bg-white/5 rounded-xl transition-all cursor-pointer border border-transparent hover:border-white/10">
                                <div class="flex items-center gap-4">
                                    <span class="text-2xl">💻</span>
                                    <div>
                                        <p class="font-bold text-white">Software Development</p>
                                        <p class="text-xs text-gray-500">High demand positions</p>
                                    </div>
                                </div>
                                <span class="text-lg font-black text-cyan-400">↗ 25%</span>
                            </div>

                            <div class="flex items-center justify-between p-4 hover:bg-white/5 rounded-xl transition-all cursor-pointer border border-transparent hover:border-white/10">
                                <div class="flex items-center gap-4">
                                    <span class="text-2xl">🎨</span>
                                    <div>
                                        <p class="font-bold text-white">Design & UX</p>
                                        <p class="text-xs text-gray-500">Creative positions</p>
                                    </div>
                                </div>
                                <span class="text-lg font-black text-blue-400">↗ 18%</span>
                            </div>

                            <div class="flex items-center justify-between p-4 hover:bg-white/5 rounded-xl transition-all cursor-pointer border border-transparent hover:border-white/10">
                                <div class="flex items-center gap-4">
                                    <span class="text-2xl">📊</span>
                                    <div>
                                        <p class="font-bold text-white">Marketing</p>
                                        <p class="text-xs text-gray-500">Growing field</p>
                                    </div>
                                </div>
                                <span class="text-lg font-black text-green-400">↗ 12%</span>
                            </div>
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
</style>
