<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import GalaxyBackground from '@/Components/GalaxyBackground.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-white via-gray-50 to-white dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 px-4 sm:px-6 lg:px-8 overflow-x-hidden relative transition-colors duration-300">
        <!-- Galaxy Background -->
        <GalaxyBackground />

        <div class="relative z-10 mb-6 sm:mb-8">
            <Link href="/">
                <ApplicationLogo class="h-20 sm:h-24 w-auto fill-current text-gray-500 dark:text-gray-400 transition-colors duration-300" />
            </Link>
        </div>

        <div
            class="relative z-10 w-full bg-white dark:bg-slate-800 rounded-xl shadow-lg dark:shadow-2xl px-6 sm:px-8 py-8 sm:max-w-md overflow-hidden"
        >
            <slot />
        </div>
    </div>
</template>
