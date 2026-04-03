<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-4 pe-4 py-3 border-l-4 border-indigo-400 text-start text-base font-medium text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 focus:outline-none focus:text-indigo-800 dark:focus:text-indigo-300 focus:bg-indigo-100 dark:focus:bg-indigo-900/40 focus:border-indigo-700 dark:focus:border-indigo-400 transition duration-150 ease-in-out'
        : 'block w-full ps-4 pe-4 py-3 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800 hover:border-gray-300 dark:hover:border-slate-700 focus:outline-none focus:text-gray-800 dark:focus:text-gray-300 focus:bg-gray-50 dark:focus:bg-slate-800 focus:border-gray-300 dark:focus:border-slate-700 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
