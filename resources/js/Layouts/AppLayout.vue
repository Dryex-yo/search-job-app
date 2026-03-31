<script setup>
import NotificationContainer from '@/Components/NotificationContainer.vue';
</script>

<template>
    <div class="overflow-x-hidden">
        <!-- Notification Container -->
        <NotificationContainer />

        <!-- Page Content -->
        <slot />
    </div>
</template>
