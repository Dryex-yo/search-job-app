<script setup>
import NotificationContainer from '@/Components/NotificationContainer.vue';
</script>

<template>
    <div>
        <!-- Notification Container -->
        <NotificationContainer />

        <!-- Page Content -->
        <slot />
    </div>
</template>
