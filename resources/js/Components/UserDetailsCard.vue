<script setup>
import { computed } from 'vue';

defineProps({
    user: {
        type: Object,
        required: true,
    },
    showContact: {
        type: Boolean,
        default: true,
    },
});

const initials = computed(() => {
    return user.value.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase();
});
</script>

<template>
    <div class="bg-white/5 border border-white/10 rounded-lg p-6 backdrop-blur-xl">
        <!-- Header with Avatar -->
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-4">
                <!-- Avatar -->
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                    {{ initials }}
                </div>
                <!-- Name and Status -->
                <div>
                    <h3 class="text-xl font-bold text-white">{{ user.name }}</h3>
                    <p class="text-slate-400 text-sm">{{ user.email }}</p>
                </div>
            </div>
            <!-- Status Badge -->
            <div class="bg-green-500/20 border border-green-500/30 rounded-full px-3 py-1">
                <p class="text-green-400 text-xs font-semibold">✓ Profil Lengkap</p>
            </div>
        </div>

        <hr class="border-white/10 my-6">

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Email -->
            <div>
                <label class="text-slate-400 text-sm font-medium">📧 Email</label>
                <p class="text-white mt-1">{{ user.email }}</p>
            </div>

            <!-- Phone -->
            <div>
                <label class="text-slate-400 text-sm font-medium">📱 Nomor Telepon</label>
                <p class="text-white mt-1">
                    {{ user.phone || '(Belum diisi)' }}
                </p>
            </div>
        </div>

        <!-- Bio Section -->
        <div v-if="user.bio" class="mt-6 pt-6 border-t border-white/10">
            <label class="text-slate-400 text-sm font-medium">📝 Tentang Diri</label>
            <p class="text-white mt-2 leading-relaxed">{{ user.bio }}</p>
        </div>

        <!-- CV Status -->
        <div class="mt-6 pt-6 border-t border-white/10">
            <label class="text-slate-400 text-sm font-medium">📄 CV/Resume</label>
            <div v-if="user.resume_path" class="mt-2 flex items-center gap-3 bg-green-500/10 border border-green-500/30 rounded-lg p-3">
                <span class="text-green-400">✓</span>
                <span class="text-white text-sm">CV telah diupload</span>
                <a 
                    :href="`/storage/${user.resume_path}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="ml-auto text-blue-400 hover:text-blue-300 text-sm font-medium"
                >
                    Lihat →
                </a>
            </div>
            <div v-else class="mt-2 flex items-center gap-3 bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-3">
                <span class="text-yellow-400">⚠</span>
                <span class="text-white text-sm">CV belum diupload</span>
            </div>
        </div>

        <!-- Metadata -->
        <div class="mt-6 pt-6 border-t border-white/10 grid grid-cols-2 gap-4 text-xs">
            <div>
                <p class="text-slate-500">Member Sejak</p>
                <p class="text-white font-medium mt-1">
                    {{ new Date(user.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                </p>
            </div>
            <div>
                <p class="text-slate-500">Terakhir Diupdate</p>
                <p class="text-white font-medium mt-1">
                    {{ new Date(user.updated_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                </p>
            </div>
        </div>
    </div>
</template>
