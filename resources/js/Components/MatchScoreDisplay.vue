<script setup>
import { computed } from 'vue';

const props = defineProps({
    score: {
        type: [Number, null],
        default: null
    },
    analysisStatus: {
        type: String,
        default: 'pending' // 'pending', 'analyzing', 'completed', 'failed'
    }
});

/**
 * Determine the color scheme based on score value
 * 80+: Green (excellent)
 * 60-79: Yellow (good)
 * 40-59: Orange (fair)
 * <40: Red (poor)
 */
const getScoreColor = computed(() => {
    if (!props.score) return { text: 'text-gray-500', bg: 'bg-gray-600', gradient: 'from-gray-600 to-gray-700', glow: 'rgba(75, 85, 99, 0.3)' };
    if (props.score >= 80) return { text: 'text-green-400', bg: 'bg-green-500', gradient: 'from-green-500 to-emerald-500', glow: 'rgba(16, 185, 129, 0.6)' };
    if (props.score >= 60) return { text: 'text-yellow-400', bg: 'bg-yellow-500', gradient: 'from-yellow-500 to-yellow-400', glow: 'rgba(234, 179, 8, 0.6)' };
    if (props.score >= 40) return { text: 'text-orange-400', bg: 'bg-orange-500', gradient: 'from-orange-500 to-red-400', glow: 'rgba(249, 115, 22, 0.6)' };
    return { text: 'text-red-400', bg: 'bg-red-500', gradient: 'from-red-500 to-red-600', glow: 'rgba(239, 68, 68, 0.6)' };
});

/**
 * Check if currently analyzing
 */
const isAnalyzing = computed(() => props.analysisStatus === 'analyzing' || (props.score === null && props.analysisStatus !== 'pending' && props.analysisStatus !== 'failed'));

/**
 * Get display text for analysis status
 */
const getAnalysisStatusText = computed(() => {
    switch (props.analysisStatus) {
        case 'completed':
            return 'Analyzed';
        case 'analyzing':
            return 'Analyzing...';
        case 'pending':
            return 'Pending';
        case 'failed':
            return 'Failed';
        default:
            return '-';
    }
});

/**
 * Get score category label
 */
const getScoreCategory = computed(() => {
    if (!props.score) return '';
    if (props.score >= 80) return 'Excellent';
    if (props.score >= 60) return 'Good';
    if (props.score >= 40) return 'Fair';
    return 'Poor';
});
</script>

<template>
    <!-- Loading State with Pulse Animation -->
    <div v-if="isAnalyzing" class="space-y-2">
        <div class="flex items-center gap-3">
            <!-- Pulse Spinner -->
            <div class="relative flex items-center gap-2">
                <!-- Outer Pulse -->
                <div class="absolute w-6 h-6 bg-cyan-400 rounded-full opacity-30 animate-pulse"></div>
                <!-- Middle Pulse -->
                <div class="absolute w-6 h-6 bg-cyan-400 rounded-full opacity-20 animate-pulse" style="animation-delay: 0.15s;"></div>
                <!-- Inner Spinner -->
                <div class="relative w-5 h-5 border-2 border-cyan-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
            
            <!-- Analyzing Text with Spark Icon -->
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-bold text-cyan-400 tracking-wider">Analyzing</span>
                <!-- Spark Icon (Star) -->
                <span class="inline-block text-cyan-400 animate-pulse" style="font-size: 12px;">✨</span>
                <span class="text-xs font-light text-cyan-300">...</span>
            </div>
        </div>
        <!-- Subtle Pulse Bottom Line -->
        <div class="h-1 w-full bg-cyan-500/30 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-transparent via-cyan-400 to-transparent animate-pulse" style="width: 40%; animation: shimmer 2s infinite;"></div>
        </div>
    </div>

    <!-- Score Display State -->
    <div v-else-if="props.score !== null && props.score !== undefined" class="space-y-2">
        <!-- Score with Gradient Text -->
        <div class="flex items-center gap-2">
            <span :class="[getScoreColor.text, 'font-black text-lg tracking-tight']">
                {{ props.score }}
            </span>
            <span class="text-gray-500 text-xs font-semibold">/100</span>
            
            <!-- Category Badge -->
            <span v-if="getScoreCategory" :class="[
                'text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border',
                props.score >= 80 ? 'bg-green-500/20 text-green-300 border-green-400/30' :
                props.score >= 60 ? 'bg-yellow-500/20 text-yellow-300 border-yellow-400/30' :
                props.score >= 40 ? 'bg-orange-500/20 text-orange-300 border-orange-400/30' :
                'bg-red-500/20 text-red-300 border-red-400/30'
            ]">
                {{ getScoreCategory }}
            </span>
        </div>

        <!-- Animated Progress Bar with Glow Effect -->
        <div class="space-y-1.5">
            <div class="w-24 bg-gray-700/40 rounded-full h-2 overflow-hidden border border-gray-600/40 shadow-inner">
                <div 
                    :class="[`bg-gradient-to-r ${getScoreColor.gradient}`]"
                    :style="{ 
                        width: props.score + '%',
                        boxShadow: `0 0 15px ${getScoreColor.glow}, inset 0 0 10px rgba(255,255,255,0.1)`
                    }"
                    class="h-full transition-all duration-500 ease-out rounded-full"
                />
            </div>
            
            <!-- Status Text -->
            <p class="text-xs font-medium text-gray-500">{{ getAnalysisStatusText }}</p>
        </div>
    </div>

    <!-- Pending/Failed State -->
    <div v-else class="space-y-2">
        <div class="text-xs font-semibold" :class="props.analysisStatus === 'failed' ? 'text-red-400' : 'text-gray-500'">
            {{ getAnalysisStatusText }}
        </div>
    </div>
</template>

<style scoped>
@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}
</style>
