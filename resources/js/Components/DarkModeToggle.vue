<script setup>
import { useDarkMode } from '@/Composables/useDarkMode';
import { ref } from 'vue';

const { isDarkMode, toggleDarkMode } = useDarkMode();
const isAnimating = ref(false);

const handleToggle = () => {
    isAnimating.value = true;
    toggleDarkMode();
    setTimeout(() => {
        isAnimating.value = false;
    }, 300);
};
</script>

<template>
    <button
        @click.prevent="handleToggle"
        class="theme-toggle group"
        :class="{ 'scale-95': isAnimating }"
        :aria-label="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
        type="button"
        title="Toggle theme"
    >
        <!-- Sun Icon (shown in dark mode) -->
        <svg
            v-if="isDarkMode"
            class="w-5 h-5 text-yellow-500 transition-all duration-300 scale-100 rotate-0"
            :class="{ 'scale-50 -rotate-180': isAnimating }"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                fill-rule="evenodd"
                d="M10 2a1 1 0 011 1v2a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l-2.121-2.121a1 1 0 00-1.414 1.414l2.121 2.121a1 1 0 001.414-1.414zM2.05 6.464l2.121 2.121a1 1 0 001.414-1.414L3.464 5.05A1 1 0 002.05 6.464zm7-7a1 1 0 00-1 1v2a1 1 0 102 0V3a1 1 0 00-1-1zm0 16a1 1 0 00-1 1v2a1 1 0 102 0v-2a1 1 0 00-1-1zm7.07-7a1 1 0 00-1.414 0l-2.121 2.121a1 1 0 001.414 1.414l2.121-2.121a1 1 0 000-1.414zM5.05 9.464l-2.121-2.121a1 1 0 00-1.414 1.414L3.636 10.88a1 1 0 001.414-1.414z"
                clip-rule="evenodd"
            />
        </svg>

        <!-- Moon Icon (shown in light mode) -->
        <svg
            v-else
            class="w-5 h-5 text-blue-600 transition-all duration-300 scale-100 rotate-0"
            :class="{ 'scale-50 rotate-180': isAnimating }"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
            />
        </svg>
    </button>
</template>

<style scoped>
.theme-toggle {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background-color: #f0f4f8;
    border: 1px solid #e2e8f0;
    color: #1a202c;
}

.dark .theme-toggle {
    background-color: #1f2937;
    border-color: #374151;
    color: #d1d5db;
}

.theme-toggle:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 102, 255, 0.1);
    transform: translateY(-2px);
}

.dark .theme-toggle:hover {
    box-shadow: 0 4px 6px -1px rgba(34, 197, 94, 0.1);
}

.theme-toggle:active {
    transform: scale(0.92);
}

.theme-toggle:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
}

.dark .theme-toggle:focus {
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.theme-toggle svg {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
