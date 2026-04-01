<script setup>
import { computed } from 'vue';

defineProps({
    admin: {
        type: Object,
        default: null,
    },
    reviewedAt: {
        type: String,
        default: null,
    },
    status: {
        type: String,
        default: 'pending',
    },
});

const statusColor = computed(() => {
    switch (status.value) {
        case 'shortlisted':
            return 'bg-green-500/20 border-green-500/30';
        case 'rejected':
            return 'bg-red-500/20 border-red-500/30';
        case 'pending':
            return 'bg-yellow-500/20 border-yellow-500/30';
        default:
            return 'bg-slate-500/20 border-slate-500/30';
    }
});

const statusTextColor = computed(() => {
    switch (status.value) {
        case 'shortlisted':
            return 'text-green-400';
        case 'rejected':
            return 'text-red-400';
        case 'pending':
            return 'text-yellow-400';
        default:
            return 'text-slate-400';
    }
});

const statusLabel = computed(() => {
    switch (status.value) {
        case 'shortlisted':
            return 'Diterima';
        case 'rejected':
            return 'Ditolak';
        case 'pending':
            return 'Menunggu Review';
        default:
            return 'Tidak Jelas';
    }
});

const initials = computed(() => {
    if (!admin) return 'SYS';
    return admin.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase();
});

const formattedDate = computed(() => {
    if (!reviewedAt) return '-';
    return new Date(reviewedAt).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});
</script>

<template>
    <div class="bg-white/5 border border-white/10 rounded-lg p-6 backdrop-blur-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">📋 Status Aplikasi</h3>
            <div :class="`${statusColor} border rounded-full px-3 py-1`">
                <p :class="`${statusTextColor} text-sm font-semibold`">
                    {{ statusLabel }}
                </p>
            </div>
        </div>

        <hr class="border-white/10 my-4">

        <!-- Admin Review Info -->
        <div v-if="admin && reviewedAt" class="space-y-4">
            <div>
                <p class="text-slate-400 text-sm font-medium mb-2">⚙️ Diproses Oleh</p>
                <div class="flex items-center gap-3 bg-blue-500/10 border border-blue-500/30 rounded-lg p-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        {{ initials }}
                    </div>
                    <div>
                        <p class="text-white font-medium">{{ admin.name }}</p>
                        <p class="text-slate-400 text-xs">{{ admin.email }}</p>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-slate-400 text-sm font-medium mb-2">🕐 Waktu Review</p>
                <p class="text-white">{{ formattedDate }}</p>
            </div>

            <!-- Department/Role -->
            <div v-if="admin.bio">
                <p class="text-slate-400 text-sm font-medium mb-2">💼 Departemen</p>
                <p class="text-white">{{ admin.bio }}</p>
            </div>
        </div>

        <!-- No Admin Review Info -->
        <div v-else class="text-center py-6">
            <p class="text-slate-400 text-sm">
                ⏳ Aplikasi masih menunggu review dari admin
            </p>
        </div>
    </div>
</template>
