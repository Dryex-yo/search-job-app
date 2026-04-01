<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminPageLayout from '@/Layouts/AdminPageLayout.vue';

const props = defineProps({
    analytics: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head title="Dryex Admin - Jobs Management" />

    <AdminPageLayout title="Jobs Management 💼" subtitle="Manage and monitor all job listings">
        <!-- Quick Actions -->
        <div class="mb-12 grid grid-cols-1 md:grid-cols-3 gap-6">
            <button class="bg-gradient-to-br from-cyan-500/20 to-cyan-500/5 border border-cyan-500/30 hover:border-cyan-500/60 rounded-2xl p-6 text-left transition-all hover:shadow-lg hover:shadow-cyan-500/20">
                <p class="text-3xl mb-2">➕</p>
                <p class="font-bold text-white mb-1">Create New Job</p>
                <p class="text-xs text-gray-500">Add a new job listing to the platform</p>
            </button>

            <button class="bg-gradient-to-br from-blue-500/20 to-blue-500/5 border border-blue-500/30 hover:border-blue-500/60 rounded-2xl p-6 text-left transition-all hover:shadow-lg hover:shadow-blue-500/20">
                <p class="text-3xl mb-2">📋</p>
                <p class="font-bold text-white mb-1">Active Listings</p>
                <p class="text-lg font-black text-blue-400">{{ analytics.active_jobs }}</p>
            </button>

            <button class="bg-gradient-to-br from-green-500/20 to-green-500/5 border border-green-500/30 hover:border-green-500/60 rounded-2xl p-6 text-left transition-all hover:shadow-lg hover:shadow-green-500/20">
                <p class="text-3xl mb-2">🎯</p>
                <p class="font-bold text-white mb-1">Total Applications</p>
                <p class="text-lg font-black text-green-400">{{ analytics.total_applications }}</p>
            </button>
        </div>

        <!-- Job Stats Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-8 italic">Job Listings Overview</h4>
                
                <div class="space-y-8">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-sm font-bold text-gray-400">Total Jobs Created</p>
                            <p class="text-2xl font-black text-white">{{ analytics.total_jobs }}</p>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-cyan-600 to-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.7)]" style="width: 100%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-sm font-bold text-gray-400">Currently Active</p>
                            <p class="text-2xl font-black text-cyan-400">{{ analytics.active_jobs }}</p>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-cyan-600 to-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.7)]" :style="{width: Math.round((analytics.active_jobs / analytics.total_jobs) * 100) + '%' || '0%'}"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-sm font-bold text-gray-400">Closed/Archived</p>
                            <p class="text-2xl font-black text-gray-500">{{ analytics.total_jobs - analytics.active_jobs }}</p>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-gray-600 to-gray-500 shadow-[0_0_20px_rgba(107,114,128,0.7)]" :style="{width: Math.round(((analytics.total_jobs - analytics.active_jobs) / analytics.total_jobs) * 100) + '%' || '0%'}"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-8 italic">Application Distribution</h4>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⏳</span>
                            <span class="font-bold text-gray-300">Pending</span>
                        </div>
                        <span class="text-xl font-black text-orange-400">{{ analytics.pending_applications }}</span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⭐</span>
                            <span class="font-bold text-gray-300">Shortlisted</span>
                        </div>
                        <span class="text-xl font-black text-blue-400">{{ analytics.shortlisted_applications }}</span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">✅</span>
                            <span class="font-bold text-gray-300">Hired</span>
                        </div>
                        <span class="text-xl font-black text-green-400">{{ analytics.hired_count }}</span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">❌</span>
                            <span class="font-bold text-gray-300">Declined</span>
                        </div>
                        <span class="text-xl font-black text-red-400">{{ analytics.rejected_applications }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AdminPageLayout>
</template>
