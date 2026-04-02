<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Cover Letter',
    },
    coverLetter: {
        type: String,
        default: 'No cover letter provided',
    },
    candidateName: {
        type: String,
        default: 'Candidate',
    },
});

const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};

const copyCoverLetter = () => {
    navigator.clipboard.writeText(props.coverLetter).then(() => {
        alert('Surat lamaran berhasil disalin ke clipboard');
    }).catch(err => {
        alert('Gagal menyalin surat lamaran');
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div @click="closeModal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

        <div class="relative bg-white/10 dark:bg-white/5 backdrop-blur-2xl border border-white/20 dark:border-white/10 w-full max-w-2xl max-h-[80vh] rounded-[2rem] shadow-2xl text-white dark:text-gray-300 overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-white/10 dark:border-white/5 bg-white/5 dark:bg-white/[0.02]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-cyan-500/20 dark:bg-cyan-500/10 flex items-center justify-center border border-cyan-500/30 dark:border-cyan-500/20">
                        <svg class="w-5 h-5 text-cyan-400 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v9a2 2 0 01-2 2h-4l-4 4v-4z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white dark:text-white">{{ title }}</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ candidateName }}</p>
                    </div>
                </div>
                <button @click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-white dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="bg-white/5 dark:bg-white/[0.003] border border-white/10 dark:border-white/5 rounded-xl p-6">
                    <p class="text-sm leading-relaxed whitespace-pre-wrap text-gray-300 dark:text-gray-400">{{ coverLetter }}</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex gap-3 p-6 border-t border-white/10 dark:border-white/5 bg-white/5 dark:bg-white/[0.02]">
                <button 
                    @click="copyCoverLetter"
                    class="flex-1 px-4 py-2 bg-cyan-500/20 dark:bg-cyan-500/10 border border-cyan-500/30 dark:border-cyan-500/20 rounded-lg text-cyan-400 dark:text-cyan-400 hover:bg-cyan-500/30 dark:hover:bg-cyan-500/20 transition-all text-sm font-bold"
                >
                    📋 Salin Teks
                </button>
                <button 
                    @click="closeModal"
                    class="flex-1 px-4 py-2 bg-slate-500/20 dark:bg-slate-500/10 border border-slate-500/30 dark:border-slate-500/20 rounded-lg text-slate-400 dark:text-slate-400 hover:bg-slate-500/30 dark:hover:bg-slate-500/20 transition-all text-sm font-bold"
                >
                    ✕ Tutup
                </button>
            </div>
        </div>
    </div>
</template>
