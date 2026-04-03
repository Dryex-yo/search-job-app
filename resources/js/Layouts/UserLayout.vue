<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import NotificationContainer from '@/Components/NotificationContainer.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import GalaxyBackground from '@/Components/GalaxyBackground.vue';

const { t } = useI18n();
const showingNavigationDropdown = ref(false);
const page = usePage();

const auth = computed(() => {
    return page.props.auth || {};
});

const handleLogout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="overflow-x-hidden relative">
        <!-- Galaxy Background -->
        <GalaxyBackground />
        
        <!-- Notification Container -->
        <NotificationContainer />

        <div class="min-h-screen bg-gradient-to-br from-white via-gray-50 to-white dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 transition-colors duration-300 relative z-10">
            <!-- Navigation Bar -->
            <nav class="border-b border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900/50 dark:backdrop-blur-md sticky top-0 z-50 transition-colors duration-300">
                <div class="mx-auto w-full max-w-7xl px-3 sm:px-4 md:px-6 lg:px-8">
                    <div class="flex h-16 gap-2 justify-between items-center">
                        <!-- Logo -->
                        <div class="flex items-center shrink-0 min-w-0">
                            <Link href="/" class="flex items-center gap-1 shrink min-w-0">
                                <div class="flex shrink-0 w-9 h-9 items-center">
                                    <ApplicationLogo class="h-full w-auto max-w-[200px]" />
                                </div>
                                <span class="text-sm sm:text-lg font-bold text-gray-900 dark:text-white whitespace-nowrap truncate">JobSearch</span>
                            </Link>
                        </div>

                        <!-- Desktop Navigation Links -->
                        <div class="hidden sm:flex items-center gap-6 md:gap-8">
                            <NavLink
                                href="/"
                                :active="route().current('index')"
                                class="text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white transition text-sm md:text-base"
                            >
                                {{ t('navigation.jobs') }}
                            </NavLink>
                            <NavLink
                                href="/dashboard"
                                :active="route().current('dashboard')"
                                class="text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white transition text-sm md:text-base"
                            >
                                {{ t('Dashboard') }}
                            </NavLink>
                        </div>

                        <!-- Desktop User Menu / Auth Links -->
                        <div class="hidden sm:flex items-center gap-3 md:gap-4">
                            <!-- Dark Mode Toggle -->
                            <DarkModeToggle />

                            <!-- Language Switcher -->
                            <LanguageSwitcher />

                            <template v-if="auth.user">
                                <!-- User Dropdown -->
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-md border border-transparent bg-gray-200 dark:bg-slate-700 px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium leading-4 text-gray-700 dark:text-slate-100 transition duration-150 ease-in-out hover:bg-gray-300 dark:hover:bg-slate-600 hover:text-gray-900 dark:hover:text-white focus:outline-none"
                                        >
                                            <span class="truncate">{{ auth.user.name }}</span>
                                            <svg
                                                class="-me-0.5 ms-1 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </template>

                                    <template #content>
                                        <DropdownLink href="/">
                                            {{ t('navigation.jobs') }}
                                        </DropdownLink>
                                        <DropdownLink :href="route('profile.edit')">
                                            {{ t('navigation.myProfile') }}
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            {{ t('common.logout') }}
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </template>
                            <template v-else>
                                <!-- Auth Links for Guests -->
                                <Link
                                    :href="route('login')"
                                    class="text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white transition font-medium text-xs sm:text-sm"
                                >
                                    {{ t('common.login') }}
                                </Link>
                                <Link
                                    :href="route('register')"
                                    class="px-2 sm:px-4 py-2 bg-cyan-600 dark:bg-cyan-600 text-white rounded-md hover:bg-cyan-700 dark:hover:bg-cyan-700 transition font-medium text-xs sm:text-sm"
                                >
                                    {{ t('common.register') }}
                                </Link>
                            </template>
                        </div>

                        <!-- Mobile Hamburger Menu -->
                        <div class="flex sm:hidden items-center gap-1">
                            <DarkModeToggle />
                            <LanguageSwitcher />
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-lg p-2.5 text-gray-600 dark:text-slate-400 transition duration-150 ease-in-out hover:bg-gray-100 dark:hover:bg-slate-700 hover:text-gray-900 dark:hover:text-slate-100 focus:bg-gray-100 dark:focus:bg-slate-700 focus:text-gray-900 dark:focus:text-slate-100 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 transition-all duration-300 ease-smooth"
                >
                    <div class="space-y-1 px-2 pb-4 pt-3 sm:px-3">
                        <ResponsiveNavLink href="/" :active="route().current('index')" class="text-sm">
                            {{ t('navigation.jobs') }}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href="/dashboard" :active="route().current('dashboard')" class="text-sm">
                            {{ t('navigation.dashboard') }}
                        </ResponsiveNavLink>
                    </div>

                    <!-- Mobile Auth Section -->
                    <div v-if="auth.user" class="border-t border-gray-200 dark:border-slate-700 px-3 sm:px-4 py-3 transition-colors duration-300">
                        <div class="text-sm sm:text-base font-medium text-gray-900 dark:text-slate-100 truncate">
                            {{ auth.user.name }}
                        </div>
                        <div class="text-xs sm:text-sm font-medium text-gray-600 dark:text-slate-400 truncate">
                            {{ auth.user.email }}
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')" class="text-gray-600 dark:text-slate-300 text-sm">
                                {{ t('navigation.myProfile') }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="text-gray-600 dark:text-slate-300 text-sm">
                                {{ t('common.logout') }}
                            </ResponsiveNavLink>
                        </div>
                    </div>
                    <div v-else class="border-t border-gray-200 dark:border-slate-700 px-3 sm:px-4 py-3 space-y-2 transition-colors duration-300">
                        <Link
                            :href="route('login')"
                            class="block w-full text-center px-3 sm:px-4 py-2.5 text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white transition rounded-md hover:bg-gray-100 dark:hover:bg-slate-700 text-sm font-medium"
                        >
                            {{ t('common.login') }}
                        </Link>
                        <Link
                            :href="route('register')"
                            class="block w-full text-center px-3 sm:px-4 py-2.5 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 transition text-sm font-medium"
                        >
                            {{ t('common.register') }}
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="min-h-[calc(100vh-64px)]">
                <slot />
            </main>
        </div>
    </div>
</template>
