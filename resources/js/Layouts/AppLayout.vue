<script setup>
import NotificationContainer from '@/Components/NotificationContainer.vue';
import GalaxyBackground from '@/Components/GalaxyBackground.vue';
</script>

<template>
    <div class="overflow-x-hidden relative">
        <!-- Galaxy Background -->
        <GalaxyBackground />

        <!-- Notification Container -->
        <NotificationContainer />

        <!-- Page Content -->
        <slot />
    </div>
</template>
