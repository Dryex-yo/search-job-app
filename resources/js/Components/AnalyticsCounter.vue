<script setup>
import { computed } from 'vue';

const props = defineProps({
    icon: {
        type: String,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    color: {
        type: String,
        default: 'cyan'
    },
    trend: {
        type: [String, Number],
        default: null
    },
    trendDirection: {
        type: String,
        enum: ['up', 'down', 'neutral'],
        default: 'neutral'
    },
    isUpdating: {
        type: Boolean,
        default: false
    }
});

const colorMap = {
    cyan: {
        bg: 'from-cyan-500/20 to-cyan-500/5',
        border: 'border-cyan-500/30',
        text: 'text-cyan-400',
        shadow: 'shadow-cyan-500/10'
    },
    green: {
        bg: 'from-green-500/20 to-green-500/5',
        border: 'border-green-500/30',
        text: 'text-green-400',
        shadow: 'shadow-green-500/10'
    },
    blue: {
        bg: 'from-blue-500/20 to-blue-500/5',
        border: 'border-blue-500/30',
        text: 'text-blue-400',
        shadow: 'shadow-blue-500/10'
    },
    purple: {
        bg: 'from-purple-500/20 to-purple-500/5',
        border: 'border-purple-500/30',
        text: 'text-purple-400',
        shadow: 'shadow-purple-500/10'
    },
    orange: {
        bg: 'from-orange-500/20 to-orange-500/5',
        border: 'border-orange-500/30',
        text: 'text-orange-400',
        shadow: 'shadow-orange-500/10'
    },
    pink: {
        bg: 'from-pink-500/20 to-pink-500/5',
        border: 'border-pink-500/30',
        text: 'text-pink-400',
        shadow: 'shadow-pink-500/10'
    }
};

const currentColor = computed(() => colorMap[props.color] || colorMap.cyan);

const trendIcon = computed(() => {
    if (props.trendDirection === 'up') return '📈';
    if (props.trendDirection === 'down') return '📉';
    return '➡️';
});
</script>

<template>
    <div :class="[`bg-gradient-to-br ${currentColor.bg} ${currentColor.border}`, 'border rounded-[2rem] p-10 relative overflow-hidden group cursor-pointer hover:shadow-2xl transition-all duration-300', isUpdating ? 'ring-2 ring-offset-2 ring-offset-[#080B14]' : '']"
        :style="{ boxShadow: isUpdating ? `0 0 30px ${currentColor.text}, 0 20px 40px ${currentColor.shadow}` : `0 20px 40px ${currentColor.shadow}`, ringColor: currentColor.text }">
        
        <!-- Background glow effect -->
        <div class="absolute -top-1/2 -right-1/2 w-full h-full rounded-full opacity-0 group-hover:opacity-20 transition-opacity duration-300"
            :class="[currentColor.bg]"></div>

        <!-- Content -->
        <div class="relative z-10">
            <!-- Icon and Label -->
            <div class="flex items-start justify-between mb-8">
                <div class="text-4xl">{{ icon }}</div>
                <div v-if="trend" class="flex items-center gap-2 text-sm font-semibold" :class="[currentColor.text]">
                    <span>{{ trend }}%</span>
                    <span>{{ trendIcon }}</span>
                </div>
            </div>

            <!-- Value -->
            <div class="mb-2">
                <p class="text-5xl font-black text-white tracking-tighter transition-transform duration-300" :class="{ 'scale-110': isUpdating }">{{ value }}</p>
            </div>

            <!-- Label -->
            <div>
                <p class="text-xs font-black text-gray-500 uppercase tracking-[0.2em] italic">{{ label }}</p>
            </div>
        </div>

        <!-- Border accent -->
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
    </div>
</template>

<style scoped>
/* Smooth hover effect */
div:hover {
    transform: translateY(-4px);
}
</style>
