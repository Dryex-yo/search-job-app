<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AnalyticsCounter from '@/Components/AnalyticsCounter.vue';
import RecruiterPageLayout from '@/Layouts/RecruiterPageLayout.vue';
import { useRealtimeEvents } from '@/Composables/useRealtimeEvents';
import { useSoundNotification } from '@/Composables/useSoundNotification';
import { useNotification } from '@/Composables/useNotification';

const props = defineProps({
    analytics: {
        type: Object,
        required: true
    }
});

// Composables
const { listenToApplications, stopListeningToApplications } = useRealtimeEvents();
const { notify: playSound } = useSoundNotification();
const { success, info } = useNotification();

// Local state for real-time metrics with animating values
const metrics = ref({
    total_applications: props.analytics.total_applications,
    pending_applications: props.analytics.pending_applications,
    hired_count: props.analytics.hired_count,
});

// Animated display values
const displayMetrics = ref({
    total_applications: props.analytics.total_applications,
    pending_applications: props.analytics.pending_applications,
    hired_count: props.analytics.hired_count,
});

// Track recent updates for animation triggers
const recentUpdates = ref({
    total_applications: false,
    pending_applications: false,
    hired_count: false,
});

/**
 * Handle real-time application events
 */
const handleApplicationEvent = (event) => {
    const { type, data } = event;

    if (type === 'application.submitted') {
        metrics.value.total_applications += 1;
        metrics.value.pending_applications += 1;

        recentUpdates.value.total_applications = true;
        recentUpdates.value.pending_applications = true;
        setTimeout(() => {
            recentUpdates.value.total_applications = false;
            recentUpdates.value.pending_applications = false;
        }, 600);

        playSound();
        success(
            '📨 New Application!',
            `${data.user.name} applied for "${data.job.title}"`,
            5000
        );
    } else if (type === 'application.status-changed') {
        if (data.previous_status !== 'hired' && data.status === 'hired') {
            metrics.value.hired_count += 1;
            recentUpdates.value.hired_count = true;
            setTimeout(() => {
                recentUpdates.value.hired_count = false;
            }, 600);
        } else if (data.previous_status === 'hired' && data.status !== 'hired') {
            metrics.value.hired_count -= 1;
        }

        if (data.previous_status === 'pending' && data.status !== 'pending') {
            metrics.value.pending_applications -= 1;
            recentUpdates.value.pending_applications = true;
            setTimeout(() => {
                recentUpdates.value.pending_applications = false;
            }, 600);
        } else if (data.previous_status !== 'pending' && data.status === 'pending') {
            metrics.value.pending_applications += 1;
        }

        playSound();
        info(
            '📊 Status Updated',
            `${data.user.name}'s application is now "${data.status}"`,
            5000
        );
    }
};

// Animate numbers smoothly
watch(() => metrics.value, (newMetrics) => {
    const animationFrames = 30;
    let currentFrame = 0;

    const startValues = {
        total_applications: displayMetrics.value.total_applications,
        pending_applications: displayMetrics.value.pending_applications,
        hired_count: displayMetrics.value.hired_count,
    };

    const animate = () => {
        currentFrame++;
        const progress = currentFrame / animationFrames;

        displayMetrics.value.total_applications = Math.floor(
            startValues.total_applications + 
            (newMetrics.total_applications - startValues.total_applications) * progress
        );
        displayMetrics.value.pending_applications = Math.floor(
            startValues.pending_applications + 
            (newMetrics.pending_applications - startValues.pending_applications) * progress
        );
        displayMetrics.value.hired_count = Math.floor(
            startValues.hired_count + 
            (newMetrics.hired_count - startValues.hired_count) * progress
        );

        if (currentFrame < animationFrames) {
            requestAnimationFrame(animate);
        }
    };

    animate();
}, { deep: true });

// Initialize real-time listeners
onMounted(() => {
    listenToApplications(handleApplicationEvent);
});

// Cleanup on unmount
onUnmounted(() => {
    stopListeningToApplications();
});
</script>

<template>
    <Head title="Dryex Recruiter - Dashboard" />

    <RecruiterPageLayout title="Dashboard 📊" subtitle="Your recruitment metrics at a glance">
        <!-- Analytics Summary Grid -->
        <div class="mb-12">
            <h3 class="text-sm font-black text-gray-700 dark:text-gray-400 uppercase tracking-[0.3em] mb-8 italic">📊 Recruitment Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <AnalyticsCounter 
                    icon="📋"
                    label="Total Applications"
                    :value="displayMetrics.total_applications"
                    color="blue"
                    :isUpdating="recentUpdates.total_applications"
                />
                <AnalyticsCounter 
                    icon="⏳"
                    label="Pending Review"
                    :value="displayMetrics.pending_applications"
                    color="orange"
                    :isUpdating="recentUpdates.pending_applications"
                />
                <AnalyticsCounter 
                    icon="✅"
                    label="Hired"
                    :value="displayMetrics.hired_count"
                    color="green"
                    :isUpdating="recentUpdates.hired_count"
                />
                <AnalyticsCounter 
                    icon="🎯"
                    label="Success Rate"
                    :value="props.analytics.success_rate"
                    unit="%"
                    color="purple"
                />
            </div>
        </div>

        <!-- Quick Access -->
        <div class="mb-12">
            <h3 class="text-sm font-black text-gray-700 dark:text-gray-400 uppercase tracking-[0.3em] mb-6 italic">⚡ Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Link :href="route('recruiter.applicants')"
                    class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-500/10 dark:to-blue-600/10 border border-blue-200 dark:border-blue-500/30 rounded-2xl hover:shadow-lg transition-all duration-300 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-bold text-blue-900 dark:text-blue-300 mb-1">View All Applicants</h4>
                            <p class="text-sm text-blue-700 dark:text-blue-400">Review and manage candidate applications</p>
                        </div>
                        <span class="text-4xl group-hover:scale-110 transition-transform">👥</span>
                    </div>
                </Link>

                <Link :href="route('recruiter.analytics')"
                    class="p-6 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-500/10 dark:to-purple-600/10 border border-purple-200 dark:border-purple-500/30 rounded-2xl hover:shadow-lg transition-all duration-300 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-bold text-purple-900 dark:text-purple-300 mb-1">View Analytics</h4>
                            <p class="text-sm text-purple-700 dark:text-purple-400">Detailed charts and recruitment insights</p>
                        </div>
                        <span class="text-4xl group-hover:scale-110 transition-transform">📈</span>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Key Stats -->
        <div class="mb-8">
            <h3 class="text-sm font-black text-gray-700 dark:text-gray-400 uppercase tracking-[0.3em] mb-6 italic">📊 Additional Metrics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Total Candidates</h4>
                        <span class="text-2xl">🎯</span>
                    </div>
                    <p class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ props.analytics.total_candidates }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Unique candidates reviewed</p>
                </div>

                <div class="p-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Shortlisted</h4>
                        <span class="text-2xl">⭐</span>
                    </div>
                    <p class="text-3xl font-black text-yellow-600 dark:text-yellow-400">{{ props.analytics.shortlisted_applications }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Candidates in selection</p>
                </div>

                <div class="p-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Rejected</h4>
                        <span class="text-2xl">❌</span>
                    </div>
                    <p class="text-3xl font-black text-red-600 dark:text-red-400">{{ props.analytics.rejected_applications }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Not selected candidates</p>
                </div>
            </div>
        </div>

        <!-- Welcome Message -->
        <div class="p-8 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-500/10 dark:to-purple-500/10 border border-blue-200 dark:border-blue-500/30 rounded-2xl">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Welcome back, Recruiter! 👋</h3>
            <p class="text-gray-700 dark:text-gray-300">
                You have <strong>{{ displayMetrics.pending_applications }}</strong> applications awaiting review. 
                Navigate to the Applicants section to start reviewing candidates and updating their statuses.
            </p>
        </div>
    </RecruiterPageLayout>
</template>
