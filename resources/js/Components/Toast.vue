<script setup>
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    id: {
        type: Number,
        required: true,
    },
    type: {
        type: String,
        default: 'info',
        validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        default: '',
    },
    duration: {
        type: Number,
        default: 5000,
    },
});

const emit = defineEmits(['close']);
const isClosing = ref(false);

const getIcon = () => {
    switch (props.type) {
        case 'success':
            return '✓';
        case 'error':
            return '✕';
        case 'warning':
            return '⚠';
        case 'info':
            return 'ℹ';
        default:
            return '•';
    }
};

const getColors = computed(() => {
    switch (props.type) {
        case 'success':
            return {
                bg: 'bg-green-500/10',
                border: 'border-green-500/30',
                icon: 'text-green-400',
                title: 'text-green-300',
                text: 'text-green-100/80',
            };
        case 'error':
            return {
                bg: 'bg-red-500/10',
                border: 'border-red-500/30',
                icon: 'text-red-400',
                title: 'text-red-300',
                text: 'text-red-100/80',
            };
        case 'warning':
            return {
                bg: 'bg-yellow-500/10',
                border: 'border-yellow-500/30',
                icon: 'text-yellow-400',
                title: 'text-yellow-300',
                text: 'text-yellow-100/80',
            };
        case 'info':
            return {
                bg: 'bg-cyan-500/10',
                border: 'border-cyan-500/30',
                icon: 'text-cyan-400',
                title: 'text-cyan-300',
                text: 'text-cyan-100/80',
            };
        default:
            return {
                bg: 'bg-gray-500/10',
                border: 'border-gray-500/30',
                icon: 'text-gray-400',
                title: 'text-gray-300',
                text: 'text-gray-100/80',
            };
    }
});

const closeNotification = () => {
    isClosing.value = true;
    setTimeout(() => {
        emit('close');
    }, 200);
};

onMounted(() => {
    if (props.duration > 0) {
        const timer = setTimeout(() => {
            closeNotification();
        }, props.duration);

        return () => clearTimeout(timer);
    }
});
</script>

<template>
    <div :class="[
        'notification-item',
        {
            'opacity-0 translate-x-full': isClosing,
            'opacity-100 translate-x-0': !isClosing,
        }
    ]"
        class="transform transition-all duration-200 ease-in-out">
        
        <div :class="[getColors.bg, getColors.border]"
            class="backdrop-blur-xl border rounded-xl p-4 pr-12 shadow-lg max-w-sm">
            
            <div class="flex gap-4">
                <!-- Icon -->
                <div :class="getColors.icon" class="text-xl font-bold flex-shrink-0 mt-0.5 w-6 h-6 flex items-center justify-center">
                    {{ getIcon() }}
                </div>

                <!-- Content -->
                <div class="flex-grow min-w-0">
                    <div :class="getColors.title" class="font-bold text-sm">
                        {{ title }}
                    </div>
                    <div v-if="message" :class="getColors.text" class="text-xs mt-1 leading-relaxed">
                        {{ message }}
                    </div>
                </div>
            </div>

            <!-- Close button -->
            <button @click="closeNotification"
                class="absolute top-3 right-3 text-gray-400 hover:text-white transition-colors opacity-60 hover:opacity-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Progress bar -->
            <div v-if="duration > 0" 
                class="absolute bottom-0 left-0 h-1 bg-gradient-to-r rounded-full transition-all"
                :class="{
                    'from-green-500 to-green-600': type === 'success',
                    'from-red-500 to-red-600': type === 'error',
                    'from-yellow-500 to-yellow-600': type === 'warning',
                    'from-cyan-500 to-cyan-600': type === 'info',
                }"
                :style="`width: ${100 - ((Date.now() - (Date.now() - duration)) / duration) * 100}%;`">
            </div>
        </div>
    </div>
</template>

<style scoped>
.notification-item {
    position: relative;
}
</style>
