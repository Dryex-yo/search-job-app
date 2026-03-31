<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import NotificationContainer from '@/Components/NotificationContainer.vue';

const showingNavigationDropdown = ref(false);

const auth = computed(() => {
    return window.$page?.props?.auth || {};
});

const handleLogout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <!-- Notification Container -->
        <NotificationContainer />

        <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
            <!-- Navigation Bar -->
            <nav class="border-b border-slate-700 bg-slate-900/50 backdrop-blur-md sticky top-0 z-50">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between items-center">
                        <!-- Logo -->
                        <div class="flex shrink-0 items-center">
                            <Link href="/" class="flex items-center gap-2">
                                <ApplicationLogo class="h-8 w-8 text-cyan-400" />
                                <span class="text-lg font-bold text-white hidden sm:inline">JobSearch</span>
                            </Link>
                        </div>

                        <!-- Desktop Navigation Links -->
                        <div class="hidden sm:flex items-center gap-8">
                            <NavLink
                                href="/"
                                :active="route().current('index')"
                                class="text-slate-300 hover:text-white transition"
                            >
                                Jobs
                            </NavLink>
                            <NavLink
                                href="/dashboard"
                                :active="route().current('dashboard')"
                                class="text-slate-300 hover:text-white transition"
                            >
                                Dashboard
                            </NavLink>
                        </div>

                        <!-- Desktop User Menu / Auth Links -->
                        <div class="hidden sm:flex items-center gap-4">
                            <template v-if="auth.user">
                                <!-- User Dropdown -->
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-transparent bg-slate-700 px-3 py-2 text-sm font-medium leading-4 text-slate-100 transition duration-150 ease-in-out hover:bg-slate-600 hover:text-white focus:outline-none"
                                        >
                                            {{ auth.user.name }}
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
                                    </template>

                                    <template #content>
                                        <DropdownLink href="/">
                                            Jobs
                                        </DropdownLink>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </template>
                            <template v-else>
                                <!-- Auth Links for Guests -->
                                <Link
                                    :href="route('login')"
                                    class="text-slate-300 hover:text-white transition font-medium"
                                >
                                    Login
                                </Link>
                                <Link
                                    :href="route('register')"
                                    class="px-4 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 transition font-medium"
                                >
                                    Register
                                </Link>
                            </template>
                        </div>

                        <!-- Mobile Hamburger Menu -->
                        <div class="flex sm:hidden items-center">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition duration-150 ease-in-out hover:bg-slate-700 hover:text-slate-100 focus:bg-slate-700 focus:text-slate-100 focus:outline-none"
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
                    class="sm:hidden border-t border-slate-700 bg-slate-800"
                >
                    <div class="space-y-1 px-2 pb-3 pt-2">
                        <ResponsiveNavLink href="/" :active="route().current('index')">
                            Jobs
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href="/dashboard" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <!-- Mobile Auth Section -->
                    <div v-if="auth.user" class="border-t border-slate-700 px-4 py-4">
                        <div class="text-base font-medium text-slate-100">
                            {{ auth.user.name }}
                        </div>
                        <div class="text-sm font-medium text-slate-400">
                            {{ auth.user.email }}
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                    <div v-else class="border-t border-slate-700 px-4 py-4 space-y-2">
                        <Link
                            :href="route('login')"
                            class="block w-full text-center px-4 py-2 text-slate-300 hover:text-white transition rounded-md hover:bg-slate-700"
                        >
                            Login
                        </Link>
                        <Link
                            :href="route('register')"
                            class="block w-full text-center px-4 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 transition"
                        >
                            Register
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
