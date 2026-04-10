<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import GalaxyBackground from '@/Components/GalaxyBackground.vue';
import { Link } from '@inertiajs/vue3';

const { t } = useI18n();
const page = usePage();
const showingNavigationDropdown = ref(false);

// Computed properties for user profile
const userFullName = computed(() => {
    return page.props.auth.user?.name || 'User';
});

const userInitials = computed(() => {
    const name = page.props.auth.user?.name || '';
    if (!name) return 'U';
    const names = name.split(' ');
    const initials = (names[0]?.charAt(0) || '') + (names[1]?.charAt(0) || '');
    return initials.toUpperCase();
});

const profilePhotoUrl = computed(() => {
    return page.props.auth.user?.profile_photo_path 
        ? `/storage/${page.props.auth.user.profile_photo_path}` 
        : null;
});

const isAdmin = computed(() => {
    const user = page.props.auth.user;
    return user?.role === 'admin' || user?.roles?.includes('admin');
});

const isAuthenticated = computed(() => {
    return !!page.props.auth.user?.id;
});
</script>

<template>
    <div class="overflow-x-hidden relative">
        <!-- Galaxy Background -->
        <GalaxyBackground />
        
        <!-- Main Container with gradient background -->
        <div class="min-h-screen bg-gradient-light dark:bg-deep-blue transition-all duration-500 relative z-10">
            <!-- Navigation Bar with new styling -->
            <nav
                class="nav-light border-b sticky top-0 z-40"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto w-full max-w-7xl px-3 sm:px-6 lg:px-8">
                    <div class="flex h-16 gap-2 justify-between items-center">
                        <div class="flex items-center shrink-0 min-w-0">
                            <!-- Logo -->
                            <div class="flex shrink-0 w-9 h-9 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="h-full w-auto max-w-[200px] fill-current text-light-gray-text dark:text-white transition-colors duration-300"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    {{ t('Dashboard') }}
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center gap-4">
                            <!-- Dark Mode Toggle -->
                            <DarkModeToggle />

                            <!-- Language Switcher -->
                            <LanguageSwitcher />

                            <!-- Settings Dropdown -->
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-2 rounded-lg border border-light-gray-border dark:border-gray-700 bg-light-tertiary dark:bg-gray-800 px-2 sm:px-3 py-2 text-sm font-medium leading-4 text-light-gray-text dark:text-gray-400 transition-all duration-300 ease-smooth hover:bg-light-gray-border dark:hover:bg-gray-700 hover:text-light-gray-text dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-light-accent dark:focus:ring-offset-gray-900"
                            >
                                                <!-- Profile Photo or Avatar -->
                                                <div 
                                                    v-if="profilePhotoUrl"
                                                    class="w-7 h-7 rounded overflow-hidden flex-shrink-0"
                                                >
                                                    <img 
                                                        :src="profilePhotoUrl" 
                                                        :alt="userFullName"
                                                        class="w-full h-full object-cover"
                                                    />
                                                </div>
                                                <div 
                                                    v-else
                                                    class="w-7 h-7 rounded bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center font-bold text-white text-xs flex-shrink-0"
                                                >
                                                    {{ userInitials }}
                                                </div>
                                                <span class="hidden sm:inline truncate">{{ userFullName }}</span>

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
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
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Profile Header -->
                                        <div class="px-4 py-3 border-b border-light-gray-border dark:border-gray-700 flex items-center gap-3">
                                            <!-- Profile Photo or Avatar in Dropdown Header -->
                                            <div>
                                                <div 
                                                    v-if="profilePhotoUrl"
                                                    class="w-10 h-10 rounded overflow-hidden flex-shrink-0"
                                                >
                                                    <img 
                                                        :src="profilePhotoUrl" 
                                                        :alt="userFullName"
                                                        class="w-full h-full object-cover"
                                                    />
                                                </div>
                                                <div 
                                                    v-else
                                                    class="w-10 h-10 rounded bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center font-bold text-white flex-shrink-0"
                                                >
                                                    {{ userInitials }}
                                                </div>
                                            </div>
                                            <!-- User Details -->
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-light-gray-text dark:text-gray-100 truncate">{{ userFullName }}</p>
                                                <p class="text-xs text-light-gray-muted dark:text-gray-400 truncate">{{ page.props.auth.user?.email }}</p>
                                            </div>
                                        </div>

                                        <!-- Dashboard Menu - Only for authenticated users -->
                                        <DropdownLink
                                            v-if="isAuthenticated && isAdmin"
                                            :href="route('admin.dashboard')"
                                            class="flex items-center gap-2"
                                        >
                                            📊 {{ t('Dashboard') }}
                                        </DropdownLink>

                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            {{ t('navigation.myProfile') }}
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            {{ t('common.logout') }}
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="flex items-center gap-1 sm:hidden">
                            <!-- Dark Mode Toggle Mobile -->
                            <DarkModeToggle />

                            <!-- Language Switcher Mobile -->
                            <LanguageSwitcher />

                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-lg p-2.5 text-light-gray-muted dark:text-gray-500 transition-all duration-300 ease-smooth hover:bg-light-tertiary dark:hover:bg-gray-800 hover:text-light-gray-text dark:hover:text-gray-400 focus:bg-light-tertiary dark:focus:bg-gray-800 focus:text-light-gray-text dark:focus:text-gray-400 focus:outline-none focus:ring-2 focus:ring-light-accent dark:focus:ring-blue-500"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
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

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden border-t border-light-gray-border dark:border-gray-700 transition-all duration-300 ease-smooth"
                >
                    <div class="space-y-1 px-2 pb-4 pt-3 sm:px-3">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-light-gray-border dark:border-gray-700 pb-2 pt-4"
                    >
                        <!-- User Profile Section -->
                        <div class="px-4 flex items-center gap-3 mb-4">
                            <!-- Profile Photo or Avatar -->
                            <div>
                                <div 
                                    v-if="profilePhotoUrl"
                                    class="w-12 h-12 rounded overflow-hidden flex-shrink-0"
                                >
                                    <img 
                                        :src="profilePhotoUrl" 
                                        :alt="userFullName"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div 
                                    v-else
                                    class="w-12 h-12 rounded bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center font-bold text-white flex-shrink-0"
                                >
                                    {{ userInitials }}
                                </div>
                            </div>
                            <!-- User Details -->
                            <div class="flex-1 min-w-0">
                                <div
                                    class="text-base font-medium text-light-gray-text dark:text-gray-100 truncate"
                                >
                                    {{ userFullName }}
                                </div>
                                <div class="text-sm font-medium text-light-gray-muted dark:text-gray-400 truncate">
                                    {{ page.props.auth.user?.email }}
                                </div>
                            </div>
                        </div>

                        <!-- Dashboard Link for Admin Users -->
                        <div v-if="isAuthenticated && isAdmin" class="px-2 mb-2">
                            <ResponsiveNavLink :href="route('admin.dashboard')" class="flex items-center gap-2">
                                📊 Dashboard
                            </ResponsiveNavLink>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                {{ t('navigation.myProfile') }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-md shadow-light-md dark:shadow-dark-lg border-b border-light-gray-border dark:border-gray-700 transition-all duration-300"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="transition-all duration-300">
                <slot />
            </main>
        </div>
    </div>
</template>
