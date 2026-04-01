<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UserProfileForm from './Partials/UserProfileForm.vue';
import AdminProfileForm from './Partials/AdminProfileForm.vue';
import CVUploadForm from './Partials/CVUploadForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;
const isAdmin = computed(() => user.role === 'admin');
const isUser = computed(() => user.role === 'user');
</script>

<template>
    <Head title="Profile" />

    <UserLayout>
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ isAdmin ? '⚙️ Pengaturan Admin' : '👤 Profil Saya' }}
                        </h1>
                        <p class="text-gray-600 dark:text-gray-300">
                            {{ isAdmin ? 'Kelola profil admin dan akses sistem' : 'Kelola informasi pribadi dan CV Anda' }}
                        </p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900/30 border border-blue-300 dark:border-blue-700 rounded-lg px-4 py-2">
                        <p class="text-blue-700 dark:text-blue-300 font-medium text-sm">
                            {{ isAdmin ? '🔐 Admin' : '👥 User' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Profile Information Section -->
                <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-[2rem] p-8 backdrop-blur-xl">
                    <UserProfileForm v-if="isUser"
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                    <AdminProfileForm v-else-if="isAdmin"
                        :status="status"
                    />
                </div>

                <!-- CV Upload Section (Only for Regular Users) -->
                <div v-if="isUser" class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-[2rem] p-8 backdrop-blur-xl">
                    <CVUploadForm :status="status" />
                </div>

                <!-- Password Update Section -->
                <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-[2rem] p-8 backdrop-blur-xl">
                    <UpdatePasswordForm />
                </div>

                <!-- Delete Account Section -->
                <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-[2rem] p-8 backdrop-blur-xl">
                    <DeleteUserForm />
                </div>

                <!-- Admin Stats (For Admins Only) -->
                <div v-if="isAdmin" class="bg-gradient-to-br from-blue-50 dark:from-blue-900/20 to-purple-50 dark:to-purple-900/20 border border-gray-200 dark:border-slate-700 rounded-[2rem] p-8 backdrop-blur-xl">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">📊 Statistik Admin</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-100 dark:bg-slate-700 rounded-lg p-4 border border-gray-300 dark:border-slate-600">
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-2">Aplikasi Diproses</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">--</p>
                        </div>
                        <div class="bg-gray-100 dark:bg-slate-700 rounded-lg p-4 border border-gray-300 dark:border-slate-600">
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-2">Diterima</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">--</p>
                        </div>
                        <div class="bg-gray-100 dark:bg-slate-700 rounded-lg p-4 border border-gray-300 dark:border-slate-600">
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-2">Ditolak</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">--</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
