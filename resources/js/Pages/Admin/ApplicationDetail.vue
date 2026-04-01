<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminPageLayout from '@/Layouts/AdminPageLayout.vue';
import CVPreviewModal from '@/Components/CVPreviewModal.vue';
import NotificationContainer from '@/Components/NotificationContainer.vue';
import { useNotification } from '@/Composables/useNotification';

// Props
const props = defineProps({
    application: {
        type: Object,
        required: true,
    }
});

// State
const showCVPreview = ref(false);
const showNotes = ref(false);
const notes = ref(props.application?.notes || '');
const statusForm = ref({
    status: props.application?.status || 'pending',
    isSaving: false,
});
const isEditingNotes = ref(false);

// Initialize notification
const { success: showSuccess, error: showError, warning: showWarning } = useNotification();

// Computed properties
const statusOptions = ['pending', 'shortlisted', 'interview', 'rejected', 'hired'];

const getStatusClass = (status) => {
    const s = status?.toLowerCase();
    switch (s) {
        case 'hired': return 'text-green-400 bg-green-400/10 border-green-400/20 shadow-[0_0_15px_rgba(74,222,128,0.1)]';
        case 'interview': return 'text-cyan-400 bg-cyan-400/10 border-cyan-400/20 shadow-[0_0_15px_rgba(34,211,238,0.1)]';
        case 'shortlisted': return 'text-blue-400 bg-blue-400/10 border-blue-400/20 shadow-[0_0_15px_rgba(59,130,246,0.1)]';
        case 'pending': return 'text-yellow-400 bg-yellow-400/10 border-yellow-400/20 shadow-[0_0_15px_rgba(250,204,21,0.1)]';
        case 'rejected': return 'text-red-400 bg-red-400/10 border-red-400/20 shadow-[0_0_15px_rgba(248,113,113,0.1)]';
        default: return 'text-gray-400 bg-gray-400/10 border-gray-400/20';
    }
};

const getStatusIcon = (status) => {
    const s = status?.toLowerCase();
    switch (s) {
        case 'hired': return '✓';
        case 'interview': return '📞';
        case 'shortlisted': return '⭐';
        case 'pending': return '⏳';
        case 'rejected': return '✕';
        default: return '?';
    }
};

// Methods
const openCVPreview = () => {
    showCVPreview.value = true;
};

const closeCVPreview = () => {
    showCVPreview.value = false;
};

const updateStatus = () => {
    if (statusForm.value.status === props.application.status) {
        showWarning('Tidak Ada Perubahan', 'Status tidak berubah dari sebelumnya.');
        return;
    }

    statusForm.value.isSaving = true;
    router.patch(route('admin.applications.update', props.application.id), {
        status: statusForm.value.status,
        notes: notes.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            statusForm.value.isSaving = false;
            const newStatus = statusForm.value.status.charAt(0).toUpperCase() + statusForm.value.status.slice(1);
            showSuccess('Berhasil Diperbarui', `Status pelamar berhasil diubah menjadi ${newStatus}`);
            isEditingNotes.value = false;
        },
        onError: (errors) => {
            statusForm.value.isSaving = false;
            showError('Gagal Memperbarui', 'Terjadi kesalahan saat memperbarui data. Silakan coba lagi.');
        }
    });
};

const saveNotes = () => {
    router.patch(route('admin.applications.update', props.application.id), {
        status: props.application.status,
        notes: notes.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess('Catatan Tersimpan', 'Catatan pelamar berhasil disimpan.');
            isEditingNotes.value = false;
        },
        onError: () => {
            showError('Gagal Menyimpan', 'Gagal menyimpan catatan. Silakan coba lagi.');
        }
    });
};

const downloadResume = () => {
    window.location.href = route('admin.applicants.download', props.application.id);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Review Application - ${application.user_name}`" />

    <!-- Notification Container -->
    <NotificationContainer />

    <!-- CV Preview Modal -->
    <CVPreviewModal 
        :show="showCVPreview"
        :cv-path="application.resume_path"
        :candidate-name="application.user_name"
        title="Pratinjau Resume Pelamar"
        @close="closeCVPreview"
    />

    <AdminPageLayout :title="`${application.user_name} 👤`" :subtitle="`Melamar untuk: ${application.job_title}`">
        <!-- Back Button & Status -->
        <div class="flex items-center justify-between mb-8">
            <Link 
                href="/admin/applicants"
                class="inline-flex items-center gap-2 text-cyan-400 hover:text-cyan-300 transition-colors text-sm"
            >
                <span>←</span>
                <span>Kembali ke Daftar</span>
            </Link>
            <div :class="['px-6 py-2 rounded-xl border font-bold text-sm flex items-center gap-2', getStatusClass(application.status)]">
                <span>{{ getStatusIcon(application.status) }}</span>
                <span class="uppercase text-xs">{{ application.status }}</span>
            </div>
        </div>
                
                <!-- Applicant Information Card -->
                <div class="bg-white/[0.003] border border-white/10 rounded-2xl p-6 space-y-4">
                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <span>👤</span> Informasi Pelamar
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs uppercase tracking-widest text-gray-500 mb-2">Email</p>
                            <p class="text-white font-semibold break-all">{{ application.user_email }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest text-gray-500 mb-2">Tanggal Aplikasi</p>
                            <p class="text-white font-semibold">{{ formatDate(application.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Job Information Card -->
                <div class="bg-white/[0.003] border border-white/10 rounded-2xl p-6 space-y-4">
                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <span>💼</span> Detail Lowongan
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs uppercase tracking-widest text-gray-500 mb-2">Posisi</p>
                            <p class="text-white font-semibold">{{ application.job_title }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest text-gray-500 mb-2">Tipe Pekerjaan</p>
                            <p class="text-white font-semibold capitalize">{{ application.job_type }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest text-gray-500 mb-2">Lokasi</p>
                            <p class="text-white font-semibold">{{ application.job_location }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest text-gray-500 mb-2">Gaji</p>
                            <p class="text-white font-semibold">Rp {{ application.job_salary?.toLocaleString('id-ID') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Resume & Cover Letter -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Resume Card -->
                    <div class="bg-white/[0.003] border border-white/10 rounded-2xl p-6">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span>📄</span> Resume
                        </h2>
                        <div class="space-y-3">
                            <p class="text-sm text-gray-400">File resume pelamar tersedia untuk ditinjau</p>
                            <div class="flex gap-2">
                                <button 
                                    @click="openCVPreview"
                                    class="flex-1 px-4 py-2 bg-cyan-500/20 border border-cyan-400/30 rounded-lg text-cyan-400 hover:bg-cyan-500/30 transition-all text-sm font-bold"
                                >
                                    👁️ Pratinjau
                                </button>
                                <button 
                                    @click="downloadResume"
                                    class="flex-1 px-4 py-2 bg-blue-500/20 border border-blue-400/30 rounded-lg text-blue-400 hover:bg-blue-500/30 transition-all text-sm font-bold"
                                >
                                    ⬇️ Unduh
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Cover Letter Card -->
                    <div class="bg-white/[0.003] border border-white/10 rounded-2xl p-6">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span>💬</span> Surat Lamaran
                        </h2>
                        <div class="bg-white/[0.003] border border-white/10 rounded-lg p-4 max-h-48 overflow-y-auto">
                            <p class="text-sm text-gray-300 whitespace-pre-wrap">{{ application.cover_letter || 'Tidak ada surat lamaran' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Status Update Section -->
                <div class="bg-white/[0.003] border border-white/10 rounded-2xl p-6 space-y-4">
                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <span>📊</span> Ubah Status
                    </h2>
                    <select 
                        v-model="statusForm.status"
                        class="w-full px-4 py-3 bg-white/[0.005] border border-white/10 rounded-lg text-white focus:border-cyan-400 focus:outline-none transition-colors"
                    >
                        <option v-for="status in statusOptions" :key="status" :value="status" class="bg-gray-900 text-white">
                            {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                        </option>
                    </select>
                </div>

                <!-- Notes Section -->
                <div class="bg-white/[0.003] border border-white/10 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <span>📝</span> Catatan & Feedback
                        </h2>
                        <button 
                            @click="isEditingNotes = !isEditingNotes"
                            class="text-xs px-3 py-1 bg-white/10 border border-white/20 rounded-lg text-white hover:bg-white/20 transition-colors"
                        >
                            {{ isEditingNotes ? '✕ Batal' : '✎ Edit' }}
                        </button>
                    </div>
                    <div v-if="isEditingNotes">
                        <textarea 
                            v-model="notes"
                            placeholder="Tambahkan catatan, feedback, atau alasan untuk status ini..."
                            class="w-full px-4 py-3 bg-white/[0.005] border border-white/10 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none transition-colors resize-none"
                            rows="4"
                        ></textarea>
                    </div>
                    <div v-else class="bg-white/[0.005] border border-white/10 rounded-lg p-4 min-h-24">
                        <p class="text-sm text-gray-300 whitespace-pre-wrap">{{ notes || 'Belum ada catatan' }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 pt-6">
                    <Link 
                        href="/admin/applicants"
                        class="flex-1 px-6 py-3 bg-white/[0.003] border border-white/20 rounded-xl text-white hover:bg-white/10 transition-all font-bold text-center"
                    >
                        ✕ Batal
                    </Link>
                    <button 
                        @click="updateStatus"
                        :disabled="statusForm.isSaving"
                        class="flex-1 px-6 py-3 bg-cyan-500/20 border border-cyan-400/50 text-cyan-400 hover:bg-cyan-500/30 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition-all font-bold"
                    >
                        <span v-if="!statusForm.isSaving">✓ Simpan Perubahan</span>
                        <span v-else>💾 Menyimpan...</span>
                    </button>
                </div>
            </AdminPageLayout>
</template>

<style scoped>
.grain-bg {
    background-image: 
        repeating-linear-gradient(
            0deg,
            rgba(255, 255, 255, 0.03) 0px,
            rgba(255, 255, 255, 0.03) 1px,
            transparent 1px,
            transparent 2px
        );
    background-size: 100% 100%;
}

.glowing-border {
    position: relative;
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.1) 0%, rgba(59, 130, 246, 0.1) 100%);
}

.glowing-border::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    padding: 1px;
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.5) 0%, rgba(59, 130, 246, 0.3) 100%);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    animation: glow 2s ease-in-out infinite;
}

@keyframes glow {
    0%, 100% { opacity: 0; }
    50% { opacity: 0.5; }
}

textarea::placeholder {
    color: rgba(107, 114, 128, 0.7);
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
