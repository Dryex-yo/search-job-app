<script setup>
import { computed } from 'vue';

// Randomize skeleton widths untuk terlihat lebih natural
const getWidths = computed(() => {
    const variants = {
        candidateName: ['w-32', 'w-40', 'w-36', 'w-38'][Math.floor(Math.random() * 4)],
        position: ['w-32', 'w-36', 'w-40', 'w-28'][Math.floor(Math.random() * 4)],
        status: ['w-20', 'w-24', 'w-22'][Math.floor(Math.random() * 3)],
        date: ['w-28', 'w-32', 'w-24'][Math.floor(Math.random() * 3)],
    };
    return variants;
});
</script>

<template>
    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
        <!-- Candidate Column -->
        <td class="px-6 py-4">
            <div class="flex items-center gap-3">
                <!-- Avatar Skeleton -->
                <div class="w-10 h-10 rounded-full bg-slate-800 animate-pulse flex-shrink-0"></div>
                <!-- Name Skeleton -->
                <div :class="['bg-slate-800 animate-pulse rounded', getWidths.candidateName, 'h-4']"></div>
            </div>
        </td>

        <!-- Position Column -->
        <td class="px-6 py-4">
            <div :class="['bg-slate-800 animate-pulse rounded', getWidths.position, 'h-4']"></div>
        </td>

        <!-- Status Column -->
        <td class="px-6 py-4">
            <div :class="['bg-slate-800 animate-pulse rounded-lg', getWidths.status, 'h-8']"></div>
        </td>

        <!-- Date Column -->
        <td class="px-6 py-4">
            <div :class="['bg-slate-800 animate-pulse rounded', getWidths.date, 'h-4']"></div>
        </td>

        <!-- Actions Column -->
        <td class="px-6 py-4">
            <div class="flex gap-2">
                <!-- CV Button Skeleton -->
                <div class="w-16 h-8 bg-slate-800 animate-pulse rounded-lg"></div>
                <!-- Letter Button Skeleton -->
                <div class="w-16 h-8 bg-slate-800 animate-pulse rounded-lg"></div>
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
