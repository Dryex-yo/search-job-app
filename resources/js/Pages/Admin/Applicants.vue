<script setup>
import { computed } from 'vue'; // Tambahkan ini
import { Head, Link } from '@inertiajs/vue3';

// Mengambil info route saat ini untuk highlight menu
const props = defineProps({
    applicants: Array
});

const currentRoute = computed(() => route().current());

// Data Dummy Pelamar (Bisa diganti dengan props dari Laravel nantinya)
const applicants = [
    { id: 1, name: 'Alex Rivera', role: 'Fullstack Developer', status: 'Interview', date: '28 Mar 2026', avatar: 'AR' },
    { id: 2, name: 'Sarah Chen', role: 'UI/UX Designer', status: 'Pending', date: '29 Mar 2026', avatar: 'SC' },
    { id: 3, name: 'Michael Tan', role: 'Marketing Lead', status: 'Rejected', date: '25 Mar 2026', avatar: 'MT' },
    { id: 4, name: 'Jessica Lee', role: 'Backend Engineer', status: 'Hired', date: '30 Mar 2026', avatar: 'JL' },
];

const menus = [
    { name: 'Dashboard', icon: '📊', route: 'admin.dashboard' },
    { name: 'Analytics', icon: '📈', route: 'admin.analytics' },
    { name: 'Jobs', icon: '💼', route: 'admin.jobs' },
    { name: 'Applicants', icon: '👥', route: 'admin.applicants' },
    { name: 'Settings', icon: '⚙️', route: 'admin.settings' },
];

const getStatusClass = (status) => {
    switch (status) {
        case 'Hired': return 'text-green-400 bg-green-400/10 border-green-400/20 shadow-[0_0_15px_rgba(74,222,128,0.1)]';
        case 'Interview': return 'text-cyan-400 bg-cyan-400/10 border-cyan-400/20 shadow-[0_0_15px_rgba(34,211,238,0.1)]';
        case 'Pending': return 'text-yellow-400 bg-yellow-400/10 border-yellow-400/20 shadow-[0_0_15px_rgba(250,204,21,0.1)]';
        case 'Rejected': return 'text-red-400 bg-red-400/10 border-red-400/20 shadow-[0_0_15px_rgba(248,113,113,0.1)]';
        default: return 'text-gray-400 bg-gray-400/10 border-gray-400/20';
    }
};
</script>

<template>
    <Head title="Dryex Admin - Applicants List" />

    <div class="fixed inset-0 bg-[#080B14] flex items-center justify-center p-4 md:p-8 font-sans overflow-hidden text-white selection:bg-cyan-500/30">
        
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none grain-bg"></div>

        <div class="absolute top-[-10%] left-[-5%] w-[600px] h-[600px] bg-cyan-500/10 blur-[130px] rounded-full animate-pulse-slow"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[700px] h-[700px] bg-blue-600/10 blur-[150px] rounded-full animate-pulse-slow"></div>

        <div class="w-full max-w-[1440px] h-full max-h-[850px] bg-white/[0.005] backdrop-blur-[60px] border border-white/20 rounded-[3.5rem] shadow-[0_40px_100px_rgba(0,0,0,0.8)] flex overflow-hidden relative z-10 glowing-border">
            
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
                        <span>{{ menu.icon }}</span>
                        {{ menu.name }}
                    </Link>
                </nav>

                <Link :href="route('logout')" method="post" as="button" class="mt-auto text-left px-6 py-4 text-gray-700 hover:text-red-400 font-bold text-xs uppercase tracking-widest transition-colors">Logout</Link>
            </aside>

            <main class="flex-grow flex flex-col overflow-hidden bg-gradient-to-br from-white/[0.005] to-transparent">
                <header class="p-12 pb-6 flex justify-between items-center">
                    <div>
                        <h2 class="text-3xl font-medium tracking-tight text-white/90 italic">Manage Applicants</h2>
                        <p class="text-gray-600 text-sm mt-1 font-medium italic leading-relaxed">Review and track your job candidates efficiently.</p>
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
                    <div class="bg-white/[0.01] border border-white/10 rounded-[3rem] p-10 shadow-inner glass-grain relative overflow-hidden">
                        <table class="w-full text-left border-separate border-spacing-y-2">
                            <thead>
                                <tr class="text-[10px] font-black text-gray-600 uppercase tracking-[0.4em] italic">
                                    <th class="pb-8 px-6 font-black uppercase tracking-[0.4em]">Candidate</th>
                                    <th class="pb-8 px-6 font-black uppercase tracking-[0.4em]">Applied Role</th>
                                    <th class="pb-8 px-6 font-black uppercase tracking-[0.4em]">Status</th>
                                    <th class="pb-8 px-6 text-right font-black uppercase tracking-[0.4em]">Date Applied</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="applicant in applicants" :key="applicant.id" class="group hover:bg-white/[0.02] transition-all duration-300">
                                    <td class="py-7 px-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-11 h-11 rounded-2xl bg-white/5 flex items-center justify-center font-black text-[10px] text-cyan-400 border border-white/10 shadow-lg group-hover:border-cyan-500/30 transition-colors">{{ applicant.avatar }}</div>
                                            <span class="font-bold text-sm tracking-tight text-white/90 group-hover:text-white transition-colors">{{ applicant.name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-7 px-6">
                                        <span class="text-gray-400 text-sm italic group-hover:text-gray-300 transition-colors">{{ applicant.role }}</span>
                                    </td>
                                    <td class="py-7 px-6">
                                        <span :class="getStatusClass(applicant.status)" class="px-5 py-2 rounded-full text-[9px] font-black uppercase tracking-widest border transition-all duration-500 shadow-sm">
                                            {{ applicant.status }}
                                        </span>
                                    </td>
                                    <td class="py-7 px-6 text-right text-gray-500 text-xs font-medium italic group-hover:text-gray-400 transition-colors">
                                        {{ applicant.date }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* EFEK TETAP KONSISTEN DENGAN DASHBOARD */
.grain-bg {
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
}

.glass-grain::after {
    content: "";
    position: absolute;
    inset: 0;
    opacity: 0.05;
    pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
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

@keyframes pulse-slow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}
.animate-pulse-slow { animation: pulse-slow 5s infinite cubic-bezier(0.4, 0, 0.6, 1); }
</style>