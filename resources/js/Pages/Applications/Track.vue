<script setup>
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';

defineProps({
    application: {
        type: Object,
        required: true
    }
});

const getStatusClass = (status) => {
    const s = status.toLowerCase();
    switch (s) {
        case 'hired': return 'text-green-400 bg-green-400/10 border-green-400/20';
        case 'interview': return 'text-cyan-400 bg-cyan-400/10 border-cyan-400/20';
        case 'shortlisted': return 'text-blue-400 bg-blue-400/10 border-blue-400/20';
        case 'pending': return 'text-yellow-400 bg-yellow-400/10 border-yellow-400/20';
        case 'rejected': return 'text-red-400 bg-red-400/10 border-red-400/20';
        default: return 'text-gray-400 bg-gray-400/10 border-gray-400/20';
    }
};

const getScoreClass = (score) => {
    if (!score) return 'text-gray-500';
    if (score >= 80) return 'text-green-400';
    if (score >= 60) return 'text-yellow-400';
    if (score >= 40) return 'text-orange-400';
    return 'text-red-400';
};

const getProgressBarClass = (score) => {
    if (!score) return 'bg-gray-600';
    if (score >= 80) return 'bg-gradient-to-r from-green-500 to-emerald-500';
    if (score >= 60) return 'bg-gradient-to-r from-yellow-500 to-yellow-400';
    if (score >= 40) return 'bg-gradient-to-r from-orange-500 to-red-400';
    return 'bg-gradient-to-r from-red-500 to-red-600';
};

const getAnalysisStatus = (status) => {
    switch (status) {
        case 'completed': return 'Analyzed';
        case 'analyzing': return 'Analyzing...';
        case 'pending': return 'Pending';
        case 'failed': return 'Failed';
        default: return '-';
    }
};
</script>

<template>
    <Head title="Application Tracking" />

    <UserLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-12 px-4">
            <div class="max-w-2xl mx-auto">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <h1 class="text-4xl font-bold text-white mb-2">Application Status</h1>
                    <p class="text-gray-400">Track your application progress</p>
                </div>

                <!-- Card -->
                <div class="bg-white/[0.01] border border-white/10 rounded-3xl p-8 shadow-2xl glassmorphism">
                    <!-- Job Info -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-white mb-2">{{ application.job_title }}</h2>
                        <p class="text-gray-400">{{ application.company_name }} • {{ application.location }}</p>
                    </div>

                    <!-- Status Badge -->
                    <div class="mb-8 pb-8 border-b border-white/10">
                        <p class="text-sm text-gray-500 mb-3 uppercase tracking-wide">Current Status</p>
                        <span :class="getStatusClass(application.status)" class="inline-block px-6 py-2 rounded-full text-sm font-bold border">
                            {{ application.status?.charAt(0).toUpperCase() + application.status?.slice(1) }}
                        </span>
                    </div>

                    <!-- AI Match Score -->
                    <div class="mb-8 pb-8 border-b border-white/10">
                        <p class="text-sm text-gray-500 mb-4 uppercase tracking-wide">AI Match Score</p>
                        <div v-if="application.ai_match_score !== null && application.ai_match_score !== undefined" class="space-y-4">
                            <div class="flex items-center gap-4">
                                <span :class="[getScoreClass(application.ai_match_score), 'text-3xl font-bold']">
                                    {{ application.ai_match_score }}
                                </span>
                                <span class="text-gray-500">/100</span>
                            </div>
                            <div class="w-full bg-gray-700/50 rounded-full h-2 overflow-hidden border border-gray-600/50">
                                <div 
                                    :class="getProgressBarClass(application.ai_match_score)"
                                    :style="{ width: application.ai_match_score + '%' }"
                                    class="h-full transition-all duration-500 shadow-lg"
                                />
                            </div>
                            <p class="text-xs text-gray-500">{{ getAnalysisStatus(application.ai_analysis_status) }}</p>
                        </div>
                        <div v-else-if="application.ai_analysis_status === 'analyzing'" class="flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-cyan-500 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-sm text-cyan-400">AI is analyzing your CV...</span>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            {{ getAnalysisStatus(application.ai_analysis_status) }}
                        </div>
                    </div>

                    <!-- Analysis Details -->
                    <div v-if="application.ai_analysis_details" class="mb-8 pb-8 border-b border-white/10">
                        <p class="text-sm text-gray-500 mb-4 uppercase tracking-wide">Analysis Details</p>
                        <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-wrap">{{ application.ai_analysis_details }}</p>
                    </div>

                    <!-- Timeline -->
                    <div>
                        <p class="text-sm text-gray-500 mb-4 uppercase tracking-wide">Timeline</p>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Applied:</span>
                                <span class="text-white">{{ new Date(application.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Last Updated:</span>
                                <span class="text-white">{{ new Date(application.updated_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
                            </div>
                            <div v-if="application.ai_analyzed_at" class="flex justify-between">
                                <span class="text-gray-400">AI Analysis:</span>
                                <span class="text-white">{{ new Date(application.ai_analyzed_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-8 bg-blue-500/10 border border-blue-500/20 rounded-2xl p-6">
                    <p class="text-sm text-blue-300">
                        ℹ️ Your application is being reviewed. AI analysis will automatically score your CV against the job requirements. Check back regularly for updates!
                    </p>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
