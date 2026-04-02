<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import AdminUserDropdown from '@/Components/AdminUserDropdown.vue';
import GalaxyBackground from '@/Components/GalaxyBackground.vue';

const showingSidebar = ref(false);
const page = usePage();

const currentRoute = computed(() => route().current());

const auth = computed(() => {
    return page.props.auth || {};
});

const menus = [
    { name: 'Dashboard', icon: '📊', route: 'admin.dashboard' },
    { name: 'Analytics', icon: '📈', route: 'admin.analytics' }, 
    { name: 'Jobs', icon: '💼', route: 'admin.jobs' },
    { name: 'Applicants', icon: '👥', route: 'admin.applicants' },
    { name: 'Settings', icon: '⚙️', route: 'admin.settings' },
];
</script>

<template>
    <div class="min-h-screen w-full bg-white dark:bg-[#080B14] flex flex-col md:flex-row font-sans text-gray-900 dark:text-white transition-colors duration-300 selection:bg-cyan-500/30 overflow-x-hidden relative">
        <!-- Galaxy Background -->
        <GalaxyBackground />
        
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none grain-bg z-10"></div>

        <!-- Background gradients -->
        <div class="absolute top-[-10%] left-[-5%] w-[300px] sm:w-[600px] h-[300px] sm:h-[600px] bg-cyan-500/10 blur-[130px] rounded-full animate-pulse-slow z-10"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[300px] sm:w-[700px] h-[300px] sm:h-[700px] bg-blue-600/10 blur-[150px] rounded-full animate-pulse-slow delay-700 z-10"></div>

        <!-- Mobile Header -->
        <div class="md:hidden sticky top-0 z-50 px-4 py-3 flex justify-between items-center border-b border-gray-200 dark:border-white/5 bg-white/50 dark:bg-white/[0.002] backdrop-blur-xl relative">
            <h1 class="text-lg sm:text-2xl font-black text-cyan-400 italic tracking-tighter uppercase">DRYEX<span class="text-gray-900 dark:text-white">.</span></h1>
            <button
                @click="showingSidebar = !showingSidebar"
                class="p-2 text-cyan-400 hover:text-cyan-600 dark:hover:text-white transition rounded-lg hover:bg-gray-100 dark:hover:bg-white/5"
            >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
            </button>
        </div>

        <!-- Main container with proper scrolling -->
        <div class="w-full flex flex-col md:flex-row overflow-hidden relative z-10">
            <!-- Sidebar -->
            <aside 
                :class="{
                    'translate-x-0': showingSidebar,
                    '-translate-x-full md:translate-x-0': !showingSidebar,
                    'fixed md:static z-40 md:z-auto': true,
                }"
                class="inset-y-0 left-0 md:inset-auto w-64 sm:w-80 md:w-80 border-r border-gray-200 dark:border-white/5 p-6 sm:p-8 md:p-12 flex flex-col bg-white/[0.5] dark:bg-white/[0.002] backdrop-blur-3xl transition-transform duration-300 overflow-y-auto"
            >
                <div class="mb-8 sm:mb-16 hidden md:block">
                    <h1 class="text-2xl sm:text-3xl font-black text-cyan-400 italic tracking-tighter uppercase">DRYEX<span class="text-gray-900 dark:text-white">.</span></h1>
                </div>

                <nav class="flex-grow space-y-2 sm:space-y-3">
                    <Link v-for="menu in menus" :key="menu.name"
                        :href="route(menu.route)"
                        @click="showingSidebar = false"
                        :class="[
                            'flex items-center gap-3 sm:gap-4 px-4 sm:px-6 py-2 sm:py-3 rounded-2xl transition-all duration-300 group relative text-sm sm:text-base',
                            currentRoute === menu.route 
                                ? 'bg-cyan-100 dark:bg-white/10 text-cyan-600 dark:text-cyan-400 shadow-lg shadow-cyan-500/20 border border-cyan-400/30'
                                : 'text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 border border-transparent'
                        ]"
                    >
                        <span class="text-lg sm:text-xl">{{ menu.icon }}</span>
                        <span class="font-semibold truncate">{{ menu.name }}</span>
                        <div
                            :class="[
                                'absolute inset-0 rounded-2xl blur opacity-0 group-hover:opacity-100 transition duration-300 -z-10',
                                currentRoute === menu.route ? 'bg-cyan-500/20' : 'bg-cyan-500/10'
                            ]"
                        />
                    </Link>
                </nav>

                <!-- Divider -->
                <div class="border-t border-gray-200 dark:border-white/10 mt-6 sm:mt-8 pt-4 sm:pt-6">
                    <p class="text-xs text-gray-500 dark:text-gray-600 font-semibold uppercase tracking-widest px-4 sm:px-6">Use profile menu for logout →</p>
                </div>
            </aside>

            <!-- Close sidebar when clicking outside on mobile -->
            <div
                v-if="showingSidebar"
                @click="showingSidebar = false"
                class="md:hidden fixed inset-0 bg-black/20 dark:bg-black/40 backdrop-blur-sm z-30 transition-colors duration-300"
            />

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden w-full">
                <!-- Header -->
                <header class="border-b border-gray-200 dark:border-white/5 px-4 sm:px-6 md:px-8 py-4 sm:py-6 flex justify-between items-center bg-white/[0.5] dark:bg-white/[0.002] backdrop-blur-2xl transition-colors duration-300 relative z-10">
                    <div>
                        <h1 class="text-lg sm:text-2xl font-bold text-gray-900 dark:text-white">
                            <slot name="title">Dashboard</slot>
                        </h1>
                    </div>
                    <div class="flex items-center gap-3 sm:gap-4">
                        <!-- Dark Mode Toggle -->
                        <DarkModeToggle />

                        <!-- User Profile Dropdown -->
                        <AdminUserDropdown />
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-auto">
                    <div class="p-4 sm:p-6 md:p-8">
                        <slot />
                    </div>
                </main>
            </div>
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
    animation: pulse-slow 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.delay-700 {
    animation-delay: 700ms;
}

/* Adaptive styling for DarkModeToggle in AdminLayout - matches Jobs/UserLayout behavior */
/* Light mode: Use default light gray styling */
:deep(.theme-toggle) {
    background-color: #f0f4f8 !important;
    border-color: #e2e8f0 !important;
    color: #1a202c !important;
}

:deep(.theme-toggle:hover) {
    background-color: #e8ecf1 !important;
    box-shadow: 0 4px 6px -1px rgba(0, 102, 255, 0.1) !important;
}

:deep(.theme-toggle:focus) {
    box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1) !important;
}

/* Dark mode: Use transparent white for visibility on very dark background */
:deep(.dark .theme-toggle) {
    background-color: rgba(255, 255, 255, 0.15) !important;
    border-color: rgba(255, 255, 255, 0.25) !important;
    color: rgba(255, 255, 255, 0.9) !important;
}

:deep(.dark .theme-toggle:hover) {
    background-color: rgba(255, 255, 255, 0.2) !important;
    box-shadow: 0 4px 6px -1px rgba(6, 182, 212, 0.2) !important;
}

:deep(.dark .theme-toggle:focus) {
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.2) !important;
}

:deep(.dark .theme-toggle svg) {
    color: rgba(255, 255, 255, 0.9) !important;
}
</style>
