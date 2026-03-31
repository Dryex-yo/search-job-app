<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const showingSidebar = ref(false);

const currentRoute = computed(() => route().current());

const auth = computed(() => {
    return window.$page?.props?.auth || {};
});

const menus = [
    { name: 'Dashboard', icon: '📊', route: 'admin.dashboard' },
    { name: 'Analytics', icon: '📈', route: 'admin.analytics' }, 
    { name: 'Jobs', icon: '💼', route: 'admin.jobs' },
    { name: 'Applicants', icon: '👥', route: 'admin.applicants' },
    { name: 'Settings', icon: '⚙️', route: 'admin.settings' },
];

const handleLogout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="fixed inset-0 bg-[#080B14] flex flex-col md:flex-row items-stretch p-4 md:p-8 font-sans overflow-auto text-white selection:bg-cyan-500/30">
        
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none grain-bg"></div>

        <!-- Background gradients -->
        <div class="absolute top-[-10%] left-[-5%] w-[600px] h-[600px] bg-cyan-500/10 blur-[130px] rounded-full animate-pulse-slow"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[700px] h-[700px] bg-blue-600/10 blur-[150px] rounded-full animate-pulse-slow delay-700"></div>

        <!-- Mobile Header -->
        <div class="md:hidden absolute top-4 left-4 right-4 flex justify-between items-center z-50">
            <h1 class="text-2xl font-black text-cyan-400 italic tracking-tighter uppercase">DRYEX<span class="text-white">.</span></h1>
            <button
                @click="showingSidebar = !showingSidebar"
                class="p-2 text-cyan-400 hover:text-white transition"
            >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
            </button>
        </div>

        <!-- Main container with proper scrolling -->
        <div class="w-full h-full max-w-[1440px] mx-auto flex overflow-hidden">
            <!-- Sidebar -->
            <aside 
                :class="{
                    'translate-x-0': showingSidebar,
                    '-translate-x-full': !showingSidebar,
                    'absolute md:relative': true,
                }"
                class="fixed md:static inset-y-0 left-0 w-80 md:w-80 border-r border-white/5 p-12 flex flex-col bg-white/[0.002] backdrop-blur-3xl backdrop-xl: transition-transform duration-300 z-40 md:z-auto md:translate-x-0 md:border-r md:border-white/5 overflow-y-auto"
            >
                <div class="mb-16 hidden md:block">
                    <h1 class="text-3xl font-black text-cyan-400 italic tracking-tighter uppercase">DRYEX<span class="text-white">.</span></h1>
                </div>

                <nav class="flex-grow space-y-3">
                    <Link v-for="menu in menus" :key="menu.name"
                        :href="route(menu.route)"
                        :class="[
                            'flex items-center gap-4 px-6 py-3 rounded-2xl transition-all duration-300 group relative',
                            currentRoute === menu.route 
                                ? 'bg-white/10 text-cyan-400 shadow-lg shadow-cyan-500/20 border border-cyan-400/30'
                                : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'
                        ]"
                    >
                        <span class="text-xl">{{ menu.icon }}</span>
                        <span class="font-semibold truncate">{{ menu.name }}</span>
                        <div
                            :class="[
                                'absolute inset-0 rounded-2xl blur opacity-0 group-hover:opacity-100 transition duration-300 -z-10',
                                currentRoute === menu.route ? 'bg-cyan-500/20' : 'bg-cyan-500/10'
                            ]"
                        />
                    </Link>
                </nav>

                <!-- Logout Button -->
                <div class="border-t border-white/10 pt-6 mt-6">
                    <button
                        @click="handleLogout"
                        class="w-full flex items-center gap-4 px-6 py-3 rounded-2xl text-slate-400 hover:text-white hover:bg-red-500/10 border border-transparent hover:border-red-500/30 transition-all duration-300"
                    >
                        <span class="text-xl">🚪</span>
                        <span class="font-semibold">Logout</span>
                    </button>
                </div>
            </aside>

            <!-- Close sidebar when clicking outside on mobile -->
            <div
                v-if="showingSidebar"
                @click="showingSidebar = false"
                class="md:hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-30"
            />

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden w-full md:w-auto">
                <!-- Header -->
                <header class="border-b border-white/5 px-8 py-6 flex justify-between items-center bg-white/[0.002] backdrop-blur-2xl">
                    <div>
                        <h1 class="text-2xl font-bold text-white">
                            <slot name="title">Dashboard</slot>
                        </h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm text-white font-semibold">{{ auth.user?.name }}</p>
                            <p class="text-xs text-slate-400">{{ auth.user?.email }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center font-bold text-white">
                            {{ auth.user?.name?.charAt(0) || 'A' }}
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-auto">
                    <div class="p-8">
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
</style>
