<script setup>
import { computed, ref } from 'vue';
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

const settingsSaved = ref(false);

const handleSave = () => {
    settingsSaved.value = true;
    setTimeout(() => {
        settingsSaved.value = false;
    }, 3000);
};
</script>

<template>
    <Head title="Dryex Admin - Settings" />

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
                        <h2 class="text-3xl font-medium tracking-tight text-white/90">Settings ⚙️</h2>
                        <p class="text-gray-600 text-sm mt-1 font-medium italic">Configure platform settings and preferences</p>
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
                    <!-- Success Message -->
                    <div v-if="settingsSaved" class="mb-8 p-4 bg-green-500/20 border border-green-500/30 rounded-2xl flex items-center gap-3 animate-pulse">
                        <span class="text-2xl">✅</span>
                        <div>
                            <p class="font-bold text-green-300">Settings Saved</p>
                            <p class="text-xs text-green-400">Your changes have been saved successfully</p>
                        </div>
                    </div>

                    <!-- Platform Settings -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-10">
                        <!-- General Settings -->
                        <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                            <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-8 italic">General Settings</h4>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Platform Name</label>
                                    <input type="text" value="DRYEX" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-cyan-500/50" />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Support Email</label>
                                    <input type="email" value="support@dryex.com" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-cyan-500/50" />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Max File Upload (MB)</label>
                                    <input type="number" value="10" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-cyan-500/50" />
                                </div>

                                <button @click="handleSave" class="w-full mt-6 bg-cyan-500 hover:bg-cyan-600 text-slate-900 py-3 rounded-2xl font-bold uppercase tracking-widest transition-all">Save Changes</button>
                            </div>
                        </div>

                        <!-- Email Settings -->
                        <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                            <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-8 italic">Email Notifications</h4>
                            
                            <div class="space-y-4">
                                <label class="flex items-center gap-3 p-3 hover:bg-white/5 rounded-xl cursor-pointer transition-all">
                                    <input type="checkbox" checked class="w-5 h-5 rounded border-white/20" />
                                    <span class="font-medium">New Applications</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 hover:bg-white/5 rounded-xl cursor-pointer transition-all">
                                    <input type="checkbox" checked class="w-5 h-5 rounded border-white/20" />
                                    <span class="font-medium">Job Expiry Reminders</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 hover:bg-white/5 rounded-xl cursor-pointer transition-all">
                                    <input type="checkbox" checked class="w-5 h-5 rounded border-white/20" />
                                    <span class="font-medium">Weekly Reports</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 hover:bg-white/5 rounded-xl cursor-pointer transition-all">
                                    <input type="checkbox" class="w-5 h-5 rounded border-white/20" />
                                    <span class="font-medium">User Feedback</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner mb-10">
                        <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-8 italic">Security & Access</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="border border-white/10 rounded-2xl p-6">
                                <p class="text-sm font-bold text-gray-400 mb-4">Two-Factor Auth</p>
                                <button class="w-full bg-white/10 hover:bg-white/20 border border-white/10 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">Enable</button>
                            </div>

                            <div class="border border-white/10 rounded-2xl p-6">
                                <p class="text-sm font-bold text-gray-400 mb-4">Change Password</p>
                                <button class="w-full bg-white/10 hover:bg-white/20 border border-white/10 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">Update</button>
                            </div>

                            <div class="border border-white/10 rounded-2xl p-6">
                                <p class="text-sm font-bold text-gray-400 mb-4">Active Sessions</p>
                                <button class="w-full bg-white/10 hover:bg-white/20 border border-white/10 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">Manage</button>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="bg-gradient-to-br from-cyan-500/10 to-cyan-500/5 border border-cyan-500/30 rounded-[2.5rem] p-8">
                            <p class="text-sm font-black text-gray-600 uppercase tracking-widest mb-4">🔧 System Status</p>
                            <div class="space-y-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Status</span>
                                    <span class="text-green-400 font-bold">Operational</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Uptime</span>
                                    <span class="text-cyan-400 font-bold">99.9%</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Last Update</span>
                                    <span class="text-blue-400 font-bold">{{ new Date().toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-green-500/10 to-green-500/5 border border-green-500/30 rounded-[2.5rem] p-8">
                            <p class="text-sm font-black text-gray-600 uppercase tracking-widest mb-4">📊 Platform Stats</p>
                            <div class="space-y-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Total Users</span>
                                    <span class="text-green-400 font-bold">{{ analytics.total_users }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Active Jobs</span>
                                    <span class="text-green-400 font-bold">{{ analytics.active_jobs }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Total Apps</span>
                                    <span class="text-green-400 font-bold">{{ analytics.total_applications }}</span>
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
