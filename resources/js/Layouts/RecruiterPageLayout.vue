<script setup>
import { computed, ref } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ProfileDropdown from '@/Components/ProfileDropdown.vue';
import GalaxyBackground from '@/Components/GalaxyBackground.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

const { t } = useI18n();
const page = usePage();
const props = defineProps({
    title: {
        type: String,
        default: 'Recruiter Dashboard'
    },
    subtitle: {
        type: String,
        default: ''
    }
});

const showingSidebar = ref(false);
const currentRoute = computed(() => route().current());

// Recruiter menus - limited access
const menus = computed(() => [
    { name: t('navigation.home'), icon: '📊', route: 'recruiter.dashboard' },
    { name: t('recruiter.applicants'), icon: '👥', route: 'recruiter.applicants' },
    { name: t('recruiter.statistics'), icon: '📈', route: 'recruiter.analytics' },
]);
</script>

<template>
    <div class="overflow-x-hidden relative">
        <!-- Galaxy Background -->
        <GalaxyBackground />
        
        <!-- Main Container - Fully Responsive to dark mode -->
        <div class="min-h-screen bg-white dark:bg-slate-900 flex items-center justify-center p-4 md:p-8 font-sans overflow-hidden text-gray-900 dark:text-white selection:bg-cyan-500/30 transition-colors duration-300">
            
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none grain-bg z-10"></div>

            <div class="absolute top-[-10%] left-[-5%] w-[600px] h-[600px] bg-cyan-500/10 blur-[130px] rounded-full animate-pulse-slow z-10"></div>
            <div class="absolute bottom-[-10%] right-[-5%] w-[700px] h-[700px] bg-blue-600/10 blur-[150px] rounded-full animate-pulse-slow delay-700 z-10"></div>

            <!-- Modal Container - Responsive -->
            <div class="w-full max-w-[1440px] h-full max-h-[850px] 
                        bg-white dark:bg-slate-800 backdrop-blur-[60px] 
                        border border-gray-200 dark:border-slate-700 
                        rounded-[3.5rem] 
                        shadow-[0_40px_100px_rgba(0,0,0,0.1)] 
                        dark:shadow-[0_40px_100px_rgba(0,0,0,0.5)]
                        flex overflow-hidden relative z-20 
                        transition-all duration-300">
                
                <!-- Sidebar -->
                <aside class="w-80 border-r border-gray-200 dark:border-slate-700 p-12 flex flex-col bg-gray-50 dark:bg-slate-800 backdrop-blur-3xl hidden md:flex transition-colors duration-300">
                    <div class="mb-16">
                        <h1 class="text-3xl font-black text-blue-400 italic tracking-tighter uppercase">DRYEX<span class="text-gray-900 dark:text-white transition-colors duration-300">.</span></h1>
                        <p class="text-[10px] text-gray-600 dark:text-slate-400 uppercase tracking-widest font-bold mt-2">Recruiter Portal</p>
                    </div>

                    <nav class="flex-grow space-y-3">
                        <Link v-for="menu in menus" :key="menu.name"
                            :href="route(menu.route)"
                            :class="[currentRoute === menu.route ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-300 border-blue-400 dark:border-blue-500 shadow-sm dark:shadow-blue-500/10' : 'text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-200 border-transparent']"
                            class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl border transition-all duration-300 font-bold text-xs uppercase tracking-widest group"
                        >
                            <span class="opacity-70 group-hover:opacity-100">{{ menu.icon }}</span>
                            {{ menu.name }}
                        </Link>
                    </nav>

                    <div class="border-t border-gray-300 dark:border-slate-700 pt-6 mt-8 transition-colors duration-300">
                        <p class="text-[10px] text-gray-600 dark:text-slate-400 uppercase tracking-widest font-bold italic">Use header menu for logout →</p>
                    </div>
                </aside>

                <!-- Main Content -->
                <main class="flex-grow flex flex-col overflow-hidden bg-white dark:bg-slate-900 transition-colors duration-300">
                    <!-- Header -->
                    <header class="p-12 pb-6 flex justify-between items-center border-b border-gray-200 dark:border-slate-700 transition-colors duration-300">
                        <div>
                            <h2 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white transition-colors duration-300">{{ title }}</h2>
                            <p v-if="subtitle" class="text-gray-600 dark:text-slate-400 text-sm mt-1 font-medium italic transition-colors duration-300">{{ subtitle }}</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <!-- Dark Mode Toggle -->
                            <DarkModeToggle />

                            <!-- Language Switcher -->
                            <LanguageSwitcher />

                            <!-- Profile Dropdown -->
                            <ProfileDropdown :user="page.props.auth.user" />
                        </div>
                    </header>

                    <!-- Content Area -->
                    <div class="flex-grow p-12 pt-6 overflow-y-auto custom-scrollbar">
                        <slot />
                    </div>
                </main>
            </div>
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
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(59, 130, 246, 0.2); }

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
}

.delay-700 {
    animation-delay: 3.5s;
}
</style>
