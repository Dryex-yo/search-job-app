<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <UserLayout>
        <div class="max-w-4xl mx-auto">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-white mb-2">Profile Settings</h2>
                <p class="text-slate-400">Manage your personal information and preferences</p>
            </div>

            <div class="space-y-6">
                <div class="bg-white/5 border border-white/10 rounded-[2rem] p-8 backdrop-blur-xl">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <div class="bg-white/5 border border-white/10 rounded-[2rem] p-8 backdrop-blur-xl">
                    <UpdatePasswordForm />
                </div>

                <div class="bg-white/5 border border-white/10 rounded-[2rem] p-8 backdrop-blur-xl">
                    <DeleteUserForm />
                </div>
            </div>
        </UserLayout>
    </template>
