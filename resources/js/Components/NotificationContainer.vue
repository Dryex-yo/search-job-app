<script setup>
import { useNotification } from '@/Composables/useNotification';
import Toast from '@/Components/Toast.vue';

const { notifications, removeNotification } = useNotification();
</script>

<template>
    <div class="fixed top-6 right-6 z-[9999] space-y-3 pointer-events-none">
        <div class="pointer-events-auto">
            <TransitionGroup name="toast-list" tag="div">
                <Toast
                    v-for="notification in notifications"
                    :key="notification.id"
                    :id="notification.id"
                    :type="notification.type"
                    :title="notification.title"
                    :message="notification.message"
                    :duration="notification.duration"
                    @close="removeNotification(notification.id)"
                />
            </TransitionGroup>
        </div>
    </div>
</template>

<style scoped>
.toast-list-enter-active,
.toast-list-leave-active {
    transition: all 0.3s ease;
}

.toast-list-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-list-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.toast-list-move {
    transition: transform 0.3s ease;
}
</style>
