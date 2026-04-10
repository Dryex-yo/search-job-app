<script setup>
defineProps({
    title: {
        type: String,
        default: 'Oops! Data tidak ditemukan'
    },
    description: {
        type: String,
        default: 'Cobalah menyesuaikan filter atau pencarian Anda'
    },
    onReset: {
        type: Function,
        required: true
    },
    resetButtonText: {
        type: String,
        default: 'Reset Filter'
    }
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-16 px-6">
        <!-- SVG Illustration -->
        <div class="mb-8 relative">
            <svg class="w-48 h-48 opacity-90" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Background circle with gradient -->
                <defs>
                    <linearGradient id="bgGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#06b6d4;stop-opacity:0.1" />
                        <stop offset="100%" style="stop-color:#0ea5e9;stop-opacity:0.1" />
                    </linearGradient>
                    <linearGradient id="iconGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#06b6d4;stop-opacity:0.8" />
                        <stop offset="100%" style="stop-color:#0284c7;stop-opacity:0.8" />
                    </linearGradient>
                </defs>

                <!-- Outer circle background -->
                <circle cx="100" cy="100" r="95" fill="url(#bgGradient)" stroke="#06b6d4" stroke-width="1" opacity="0.3" />

                <!-- Middle circle -->
                <circle cx="100" cy="100" r="80" fill="none" stroke="#06b6d4" stroke-width="0.5" opacity="0.2" stroke-dasharray="5,5" />

                <!-- Empty folder/document illustration -->
                <g>
                    <!-- Outer rectangle (folder/document) -->
                    <rect x="55" y="60" width="90" height="75" rx="8" fill="none" stroke="url(#iconGradient)" stroke-width="2.5" />

                    <!-- Inner decorative lines (representing empty content) -->
                    <line x1="70" y1="80" x2="130" y2="80" stroke="url(#iconGradient)" stroke-width="1.5" opacity="0.4" />
                    <line x1="70" y1="95" x2="130" y2="95" stroke="url(#iconGradient)" stroke-width="1.5" opacity="0.3" />
                    <line x1="70" y1="110" x2="110" y2="110" stroke="url(#iconGradient)" stroke-width="1.5" opacity="0.2" />

                    <!-- Magnifying glass overlay -->
                    <circle cx="130" cy="115" r="18" fill="none" stroke="url(#iconGradient)" stroke-width="2" opacity="0.7" />
                    <line x1="143" y1="128" x2="158" y2="143" stroke="url(#iconGradient)" stroke-width="2" opacity="0.7" stroke-linecap="round" />

                    <!-- Question mark in the center -->
                    <text x="100" y="135" text-anchor="middle" font-size="48" font-weight="bold" fill="url(#iconGradient)" opacity="0.5">?</text>
                </g>

                <!-- Floating particles -->
                <circle cx="40" cy="50" r="2" fill="#06b6d4" opacity="0.3" />
                <circle cx="160" cy="70" r="1.5" fill="#0284c7" opacity="0.3" />
                <circle cx="35" cy="130" r="1" fill="#0ea5e9" opacity="0.2" />
                <circle cx="170" cy="140" r="2.5" fill="#06b6d4" opacity="0.2" />

                <!-- Animated glow elements -->
                <circle cx="100" cy="100" r="3" fill="#06b6d4" opacity="0.6">
                    <animate attributeName="r" values="3;8;3" dur="2s" repeatCount="indefinite" />
                    <animate attributeName="opacity" values="0.6;0;0.6" dur="2s" repeatCount="indefinite" />
                </circle>
            </svg>

            <!-- Floating animation style -->
            <style scoped>
                @keyframes float {
                    0%, 100% { transform: translateY(0px); }
                    50% { transform: translateY(-10px); }
                }
                .empty-state-icon {
                    animation: float 3s ease-in-out infinite;
                }
            </style>

            <div class="empty-state-icon absolute inset-0"></div>
        </div>

        <!-- Text Content -->
        <div class="text-center mb-8 max-w-md">
            <h3 class="text-2xl font-bold text-white mb-3">{{ title }}</h3>
            <p class="text-gray-400 text-sm leading-relaxed">{{ description }}</p>
        </div>

        <!-- Reset Button -->
        <button
            @click="onReset"
            class="group relative px-8 py-3 bg-gradient-to-r from-cyan-500/20 to-blue-500/20 hover:from-cyan-500/40 hover:to-blue-500/40 border border-cyan-500/50 hover:border-cyan-500/70 rounded-2xl text-sm font-bold text-cyan-400 uppercase tracking-widest transition-all duration-300 shadow-lg shadow-cyan-500/0 hover:shadow-cyan-500/20 flex items-center gap-2"
        >
            <span class="text-lg">↻</span>
            {{ resetButtonText }}
        </button>

        <!-- Decorative bottom gradient line -->
        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-cyan-500/30 to-transparent"></div>
    </div>
</template>

<style scoped>
/* Smooth animation for empty state appearance */
.empty-state-icon {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-8px);
    }
}
</style>
