<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const showDropdown = ref(false);
const page = usePage();

const auth = computed(() => {
    return page.props.auth || {};
});

const user = computed(() => {
    return auth.value.user || {};
});

// Generate user initials from full name
const userInitials = computed(() => {
    if (!user.value.name) return 'U';
    const names = user.value.name.split(' ');
    const initials = (names[0]?.charAt(0) || '') + (names[1]?.charAt(0) || '');
    return initials.toUpperCase();
});

// Get user's full name
const userFullName = computed(() => {
    return user.value.name || 'User';
});

// Get profile photo URL or null
const profilePhotoUrl = computed(() => {
    return user.value.profile_photo_path ? `/storage/${user.value.profile_photo_path}` : null;
});

// Check if user is admin
const isAdmin = computed(() => {
    return user.value.role === 'admin' || user.value.roles?.includes('admin');
});

// Check if user is authenticated
const isAuthenticated = computed(() => {
    return !!user.value.id;
});

const handleLogout = () => {
    router.post(route('logout'));
};

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const closeDropdown = () => {
    showDropdown.value = false;
};
</script>

<template>
    <div class="relative">
        <!-- Trigger Button -->
        <button
            @click="toggleDropdown"
            class="flex items-center gap-3 sm:gap-5 bg-white/[0.02] hover:bg-white/[0.05] border border-white/10 hover:border-white/20 p-2 sm:p-2.5 pr-4 sm:pr-8 rounded-2xl sm:rounded-3xl shadow-inner transition-all duration-300 cursor-pointer group"
        >
            <!-- Profile Photo or Avatar -->
            <div 
                v-if="profilePhotoUrl"
                class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl flex-shrink-0 overflow-hidden shadow-lg shadow-cyan-500/20 group-hover:shadow-cyan-500/40 transition-all"
            >
                <img 
                    :src="profilePhotoUrl" 
                    :alt="userFullName"
                    class="w-full h-full object-cover"
                />
            </div>
            <div 
                v-else
                class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center font-bold text-white text-sm sm:text-base shadow-lg shadow-cyan-500/20 group-hover:shadow-cyan-500/40 transition-all flex-shrink-0"
            >
                {{ userInitials }}
            </div>

            <!-- User Info -->
            <div class="text-left leading-tight hidden sm:flex flex-col min-w-0">
                <p class="text-xs font-black text-white truncate">{{ userFullName }}</p>
                <p class="text-[9px] text-cyan-400 uppercase tracking-[0.2em] font-black italic">
                    {{ isAdmin ? 'Administrator' : 'User' }}
                </p>
            </div>
            <span class="hidden sm:inline text-gray-500 group-hover:text-white transition-colors">
                <svg class="w-4 h-4" :class="{ 'rotate-180': showDropdown }" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5.293 7.293a1 1 0 011.414 0L12 12.586l5.293-5.293a1 1 0 111.414 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414z" />
                </svg>
            </span>
        </button>

        <!-- Dropdown Menu -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showDropdown"
                class="absolute right-0 mt-3 w-56 bg-white/[0.05] dark:bg-white/[0.08] backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl shadow-black/50 py-2 z-50"
                @click="closeDropdown"
            >
                <!-- User Info Header -->
                <div class="px-4 py-3 border-b border-white/10 flex items-center gap-3">
                    <!-- Profile Photo or Avatar in Header -->
                    <div>
                        <div 
                            v-if="profilePhotoUrl"
                            class="w-12 h-12 rounded-lg overflow-hidden shadow-md"
                        >
                            <img 
                                :src="profilePhotoUrl" 
                                :alt="userFullName"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <div 
                            v-else
                            class="w-12 h-12 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center font-bold text-white shadow-md"
                        >
                            {{ userInitials }}
                        </div>
                    </div>
                    <!-- User Details -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-white truncate">{{ userFullName }}</p>
                        <p class="text-xs text-gray-400 mt-1 truncate">{{ user.email }}</p>
                    </div>
                </div>

                <!-- Menu Items -->
                <!-- Dashboard - Only show if user is admin or authenticated -->
                <Link
                    v-if="isAuthenticated && isAdmin"
                    :href="route('admin.dashboard')"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-white/10 transition-all duration-200"
                >
                    <span>📊</span>
                    <span>Dashboard</span>
                </Link>

                <Link
                    :href="route('admin.settings')"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-white/10 transition-all duration-200"
                >
                    <span>⚙️</span>
                    <span>Settings</span>
                </Link>

                <Link
                    :href="route('admin.profile')"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-white/10 transition-all duration-200"
                    @click.prevent="alert('Profile page not found - implement if needed')"
                >
                    <span>👤</span>
                    <span>Profile</span>
                </Link>

                <!-- Divider -->
                <div class="border-t border-white/10 my-2"></div>

                <!-- Logout -->
                <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-all duration-200"
                >
                    <span>🚪</span>
                    <span>Logout</span>
                </button>
            </div>
        </Transition>

        <!-- Backdrop -->
        <div
            v-if="showDropdown"
            @click="closeDropdown"
            class="fixed inset-0 z-40"
        ></div>
    </div>
</template>
