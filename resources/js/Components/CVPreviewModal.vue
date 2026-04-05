<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Preview CV',
    },
    cvPath: {
        type: String,
        default: null,
    },
    candidateName: {
        type: String,
        default: 'Candidate',
    },
});

const emit = defineEmits(['close']);
const isLoading = ref(false);
const pdfUrl = ref(null);

watch(() => props.show, (newVal) => {
    if (newVal && props.cvPath) {
        isLoading.value = true;
        // Construct the full URL to the CV file
        pdfUrl.value = `/storage/${props.cvPath}`;
        setTimeout(() => {
            isLoading.value = false;
        }, 500);
    } else {
        pdfUrl.value = null;
    }
});

const closeModal = () => {
    emit('close');
};

const downloadCV = () => {
    if (pdfUrl.value) {
        const link = document.createElement('a');
        link.href = pdfUrl.value;
        link.download = `${props.candidateName}-CV.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div @click="closeModal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

        <div class="relative bg-white/10 dark:bg-white/5 backdrop-blur-2xl border border-white/20 dark:border-white/10 w-full max-w-4xl h-[85vh] rounded-[2rem] shadow-2xl text-white overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-white/10 bg-white/5 dark:bg-white/[0.02]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center border border-cyan-500/30">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">{{ title }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ candidateName }}</p>
                    </div>
                </div>
                <button @click="closeModal" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- PDF Viewer Area -->
            <div class="flex-grow overflow-auto bg-black/20 relative">
                <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-black/40">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-cyan-400 mx-auto mb-4"></div>
                        <p class="text-gray-300 text-sm">Loading CV...</p>
                    </div>
                </div>
                
                <iframe 
                    v-if="pdfUrl && !isLoading"
                    :src="pdfUrl" 
                    class="w-full h-full border-none"
                    title="CV Preview"
                ></iframe>

                <div v-if="!pdfUrl && !isLoading" class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <svg class="w-16 h-16 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-400">No CV available</p>
                    </div>
                </div>
            </div>

            <!-- Footer with Actions -->
            <div class="border-t border-white/10 p-4 bg-white/5 dark:bg-white/[0.02] flex justify-end gap-3">
                <button @click="closeModal"
                    class="px-6 py-2.5 rounded-lg border border-white/10 text-gray-300 hover:text-white hover:bg-white/5 transition-all text-sm font-medium uppercase tracking-wider">
                    Close
                </button>
                <button v-if="pdfUrl" @click="downloadCV"
                    class="px-6 py-2.5 rounded-lg bg-cyan-500/20 border border-cyan-500/30 text-cyan-400 hover:bg-cyan-500/30 hover:border-cyan-500/50 transition-all text-sm font-medium uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for PDF viewer */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: rgba(34, 211, 238, 0.3);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(34, 211, 238, 0.5);
}
</style>
