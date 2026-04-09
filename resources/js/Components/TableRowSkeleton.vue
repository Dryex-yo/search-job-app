<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'short', 'long'].includes(v)
    }
});

// Randomize skeleton widths untuk terlihat lebih natural
const getWidths = computed(() => {
    const variants = {
        candidateName: ['w-32', 'w-40', 'w-36', 'w-38'][Math.floor(Math.random() * 4)],
        candidateEmail: ['w-40', 'w-44', 'w-42'][Math.floor(Math.random() * 3)],
        position: ['w-32', 'w-36', 'w-40', 'w-28'][Math.floor(Math.random() * 4)],
        status: ['w-20', 'w-24', 'w-22'][Math.floor(Math.random() * 3)],
        matchScore: ['w-32', 'w-36', 'w-28'][Math.floor(Math.random() * 3)],
        date: ['w-28', 'w-32', 'w-24'][Math.floor(Math.random() * 3)],
    };
    return variants;
});
</script>

<template>
    <tr class="group hover:bg-white/[0.02] transition-all duration-300">
        <!-- Candidate Column -->
        <td class="py-7 px-6 whitespace-nowrap">
            <div class="flex items-center gap-4">
                <!-- Avatar Skeleton -->
                <div class="w-10 h-10 rounded-lg bg-slate-800 animate-pulse flex-shrink-0"></div>
                <!-- Candidate Info Skeleton -->
                <div class="min-w-0 space-y-2 flex-1">
                    <!-- Name -->
                    <div :class="['bg-slate-800 animate-pulse rounded', getWidths.candidateName, 'h-4']"></div>
                    <!-- Email -->
                    <div :class="['bg-slate-800 animate-pulse rounded', getWidths.candidateEmail, 'h-3']"></div>
                </div>
            </div>
        </td>

        <!-- Applied Role Column -->
        <td class="py-7 px-6 whitespace-nowrap">
            <div :class="['bg-slate-800 animate-pulse rounded', getWidths.position, 'h-4']"></div>
        </td>

        <!-- Status Column -->
        <td class="py-7 px-6 whitespace-nowrap">
            <div :class="['bg-slate-800 animate-pulse rounded-full', getWidths.status, 'h-6']"></div>
        </td>

        <!-- Match Score Column -->
        <td class="py-7 px-6 whitespace-nowrap">
            <div class="space-y-2">
                <div :class="['bg-slate-800 animate-pulse rounded', getWidths.matchScore, 'h-4']"></div>
                <div class="w-16 h-2 bg-slate-800 animate-pulse rounded"></div>
            </div>
        </td>

        <!-- Date Applied Column -->
        <td class="py-7 px-6 whitespace-nowrap text-right">
            <div :class="['bg-slate-800 animate-pulse rounded ml-auto', getWidths.date, 'h-3']"></div>
        </td>

        <!-- Actions Column -->
        <td class="py-7 px-6 whitespace-nowrap text-right">
            <div class="flex gap-2 justify-end flex-wrap">
                <!-- CV Button Skeleton -->
                <div class="w-16 h-8 bg-slate-800 animate-pulse rounded-lg"></div>
                <!-- Letter Button Skeleton -->
                <div class="w-16 h-8 bg-slate-800 animate-pulse rounded-lg"></div>
                <!-- Status Select Skeleton -->
                <div class="w-24 h-8 bg-slate-800 animate-pulse rounded-lg"></div>
            </div>
        </td>
    </tr>
</template>

<style scoped>
/* Ensure consistent animation timing */
:deep(.animate-pulse) {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>
