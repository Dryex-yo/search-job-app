<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import UserLayout from '../../Layouts/UserLayout.vue';
import { useNotification } from '../../Composables/useNotification';

// Props
const props = defineProps({
    job: {
        type: Object,
        required: true,
    }
});

// Initialize notification
const { success: showSuccess, error: showError, warning: showWarning } = useNotification();

// Auth check
const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);

// State
const cvFile = ref(null);
const cvFileName = ref('');
const isSubmitting = ref(false);
const showPreview = ref(false);

// Form for applying
const applyForm = useForm({
    job_id: props.job.id,
    resume: null,
    cover_letter: '',
});

// Handle CV file selection
const handleCVSelect = (event) => {
    const file = event.target.files?.[0];
    
    if (!file) {
        return;
    }

    // Validate file type
    if (file.type !== 'application/pdf') {
        showError('Format Tidak Sesuai', 'Hanya file PDF yang diperbolehkan. Silakan pilih file PDF.');
        cvFile.value = null;
        cvFileName.value = '';
        return;
    }

    // Validate file size (2MB = 2097152 bytes)
    if (file.size > 2097152) {
        showError('Ukuran File Terlalu Besar', 'Ukuran file maksimal 2MB. File Anda: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
        cvFile.value = null;
        cvFileName.value = '';
        return;
    }

    // File is valid
    cvFile.value = file;
    cvFileName.value = file.name;
};

// Clear CV selection
const clearCV = () => {
    cvFile.value = null;
    cvFileName.value = '';
    applyForm.resume = null;
    const fileInput = document.getElementById('cv-upload');
    if (fileInput) {
        fileInput.value = '';
    }
};

// Preview CV
const previewCV = () => {
    if (cvFile.value) {
        showPreview.value = true;
    }
};

// Submit application
const submitApplication = () => {
    if (!isAuthenticated.value) {
        showWarning('Login Diperlukan', 'Silakan login terlebih dahulu untuk melamar lowongan ini.');
        return;
    }

    if (!cvFile.value) {
        showError('CV Diperlukan', 'Silakan pilih file CV Anda terlebih dahulu.');
        return;
    }

    if (!applyForm.cover_letter.trim()) {
        showWarning('Surat Lamaran Kosong', 'Sebaiknya Anda menulis surat lamaran untuk meningkatkan peluang.');
    }

    isSubmitting.value = true;
    applyForm.resume = cvFile.value;

    applyForm.post(route('jobs.apply'), {
        onSuccess: () => {
            isSubmitting.value = false;
            showSuccess('Lamaran Terkirim!', 'Lamaran Anda telah berhasil dikirim. Semoga berhasil!');
            clearCV();
            applyForm.reset();
        },
        onError: (errors) => {
            isSubmitting.value = false;
            const errorMessage = Object.values(errors).flat().join(', ');
            showError('Gagal Mengirim Lamaran', errorMessage || 'Terjadi kesalahan. Silakan coba lagi.');
        }
    });
};

// Utility functions
const formatSalary = (salary) => {
    if (!salary) return 'Sesuai Kesepakatan';
    return 'Rp ' + salary.toLocaleString('id-ID');
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <UserLayout>
        <Head :title="`${job.title} - Job Detail`" />

        <div class="min-h-screen bg-gradient-to-br from-[#080B14] to-[#0f1422] text-white selection:bg-cyan-500/30 p-4 md:p-8">
            
            <!-- Background Effects -->
            <div class="fixed inset-0 opacity-[0.03] pointer-events-none grain-bg"></div>
            <div class="absolute top-[-10%] left-[-5%] w-[600px] h-[600px] bg-cyan-500/10 blur-[130px] rounded-full animate-pulse-slow"></div>
            <div class="absolute bottom-[-10%] right-[-5%] w-[700px] h-[700px] bg-blue-600/10 blur-[150px] rounded-full animate-pulse-slow"></div>

            <div class="relative z-10 max-w-4xl mx-auto">
                
                <!-- Header with Back Link -->
                <div class="mb-8 flex items-center justify-between">
                    <Link 
                        href="/"
                        class="inline-flex items-center gap-2 text-cyan-400 hover:text-cyan-300 transition-colors font-semibold"
                    >
                        <span>←</span>
                        <span>Kembali ke Daftar Lowongan</span>
                    </Link>
                </div>

                <!-- Main Content Card -->
                <div class="bg-white/[0.005] backdrop-blur-[60px] border border-white/20 rounded-[2rem] shadow-[0_40px_100px_rgba(0,0,0,0.8)] overflow-hidden">
                    
                    <!-- Job Header Section -->
                    <div class="border-b border-white/10 p-8 bg-gradient-to-r from-white/[0.01] to-transparent">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-6">
                            <div>
                                <h1 class="text-4xl md:text-5xl font-black text-white mb-3">{{ job.title }}</h1>
                                <p class="text-lg text-gray-300 mb-4">{{ job.company_name }}</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="px-4 py-2 bg-cyan-500/20 border border-cyan-400/30 text-cyan-400 rounded-full text-sm font-bold">
                                        {{ job.type }}
                                    </span>
                                    <span class="px-4 py-2 bg-blue-500/20 border border-blue-400/30 text-blue-400 rounded-full text-sm font-bold">
                                        📍 {{ job.location }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-widest text-gray-500 mb-2">Gaji</p>
                                <p class="text-2xl font-bold text-cyan-400">{{ formatSalary(job.salary) }}</p>
                            </div>
                        </div>

                        <!-- Meta Information -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-white/10">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-gray-500 mb-1">Jenis Pekerjaan</p>
                                <p class="font-semibold">{{ job.type }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest text-gray-500 mb-1">Lokasi</p>
                                <p class="font-semibold">{{ job.location }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest text-gray-500 mb-1">Dibuat</p>
                                <p class="font-semibold">{{ formatDate(job.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest text-gray-500 mb-1">Status</p>
                                <p class="font-semibold" :class="job.status === 'active' ? 'text-green-400' : 'text-gray-400'">
                                    {{ job.status === 'active' ? 'Aktif' : 'Tertutup' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 p-8">
                        
                        <!-- Job Description (Left Side) -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- Description -->
                            <div class="bg-white/[0.003] border border-white/10 rounded-2xl p-6">
                                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                                    <span>📋</span> Deskripsi Lowongan
                                </h2>
                                <p class="text-gray-300 leading-relaxed whitespace-pre-wrap">{{ job.description }}</p>
                            </div>
                        </div>

                        <!-- Apply Form (Right Side) -->
                        <div class="lg:col-span-1">
                            <div class="bg-white/[0.003] border border-white/10 rounded-2xl p-6 sticky top-8 space-y-4">
                                
                                <!-- Login Required Notice -->
                                <div v-if="!isAuthenticated" class="bg-yellow-500/10 border border-yellow-400/30 rounded-lg p-4 text-sm">
                                    <p class="text-yellow-400 font-semibold mb-2">⚠️ Login Diperlukan</p>
                                    <p class="text-yellow-300 text-xs mb-3">Silakan login atau daftar terlebih dahulu untuk melamar lowongan ini.</p>
                                    <Link 
                                        href="/login"
                                        class="block w-full px-4 py-2 bg-yellow-500/30 border border-yellow-400/50 text-yellow-400 rounded-lg text-center font-bold hover:bg-yellow-500/40 transition-colors mb-2"
                                    >
                                        Login
                                    </Link>
                                    <Link 
                                        href="/register"
                                        class="block w-full px-4 py-2 bg-white/10 border border-white/20 text-white rounded-lg text-center font-bold hover:bg-white/20 transition-colors"
                                    >
                                        Daftar
                                    </Link>
                                </div>

                                <!-- Application Form -->
                                <template v-else>
                                    <h3 class="text-lg font-bold text-white">Kirim Lamaran</h3>

                                    <!-- CV Upload Section -->
                                    <div class="space-y-3">
                                        <label class="text-sm font-semibold text-gray-300 flex items-center gap-1">
                                            <span>📄</span> Upload CV (PDF)
                                            <span class="text-red-400">*</span>
                                        </label>
                                        
                                        <div v-if="!cvFile" class="border-2 border-dashed border-cyan-400/50 rounded-lg p-4 text-center cursor-pointer hover:border-cyan-400 transition-colors"
                                             @click="() => document.getElementById('cv-upload')?.click()">
                                            <p class="text-sm text-gray-400 mb-2">📁 Drag & drop atau klik untuk memilih</p>
                                            <p class="text-xs text-gray-500">PDF, Max 2MB</p>
                                            <input 
                                                id="cv-upload"
                                                type="file" 
                                                accept=".pdf"
                                                @change="handleCVSelect"
                                                class="hidden"
                                            />
                                        </div>

                                        <!-- CV Selected State -->
                                        <div v-else class="bg-green-500/10 border border-green-400/30 rounded-lg p-4">
                                            <div class="flex items-start justify-between gap-3 mb-2">
                                                <div class="flex-grow">
                                                    <p class="text-sm font-semibold text-green-400 flex items-center gap-2">
                                                        <span>✓</span> File Dipilih
                                                    </p>
                                                    <p class="text-xs text-green-300 mt-1 break-all">{{ cvFileName }}</p>
                                                    <p class="text-xs text-green-300 mt-1">{{ (cvFile.size / 1024).toFixed(2) }} KB</p>
                                                </div>
                                                <button 
                                                    type="button"
                                                    @click="clearCV"
                                                    class="text-green-400 hover:text-red-400 transition-colors text-lg"
                                                >
                                                    ✕
                                                </button>
                                            </div>
                                            <button 
                                                type="button"
                                                @click="previewCV"
                                                class="w-full mt-2 px-3 py-1 bg-white/10 border border-white/20 text-xs rounded text-white hover:bg-white/20 transition-colors"
                                            >
                                                👁️ Preview
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Cover Letter Section -->
                                    <div class="space-y-3">
                                        <label class="text-sm font-semibold text-gray-300 flex items-center gap-1">
                                            <span>💬</span> Surat Lamaran
                                            <span class="text-gray-500">(Opsional)</span>
                                        </label>
                                        <textarea 
                                            v-model="applyForm.cover_letter"
                                            placeholder="Ceritakan mengapa Anda tertarik dengan posisi ini..."
                                            class="w-full px-4 py-3 bg-white/[0.005] border border-white/10 rounded-lg text-white text-sm placeholder-gray-600 focus:border-cyan-400 focus:outline-none transition-colors resize-none"
                                            rows="5"
                                        ></textarea>
                                    </div>

                                    <!-- Submit Button -->
                                    <button 
                                        type="button"
                                        @click="submitApplication"
                                        :disabled="isSubmitting || !cvFile"
                                        class="w-full px-6 py-3 bg-cyan-500/30 border border-cyan-400/50 text-cyan-400 font-bold rounded-lg hover:bg-cyan-500/40 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                                    >
                                        <span v-if="!isSubmitting">✓ Kirim Lamaran</span>
                                        <span v-else>⏳ Mengirim...</span>
                                    </button>

                                    <!-- Info Text -->
                                    <p class="text-xs text-gray-500 text-center">
                                        Dengan mengirim lamaran, Anda menerima Kebijakan Privasi kami.
                                    </p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PDF Preview Modal -->
            <div v-if="showPreview" class="fixed inset-0 bg-black/80 backdrop-blur z-50 flex items-center justify-center p-4" @click="showPreview = false">
                <div class="bg-[#080B14] border border-white/20 rounded-2xl max-w-3xl w-full max-h-[85vh] flex flex-col" @click.stop>
                    <div class="border-b border-white/10 p-6 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white">Preview CV</h3>
                        <button @click="showPreview = false" class="text-gray-400 hover:text-white">✕</button>
                    </div>
                    <div class="flex-grow overflow-auto">
                        <iframe 
                            v-if="cvFile"
                            :src="URL.createObjectURL(cvFile)"
                            class="w-full h-full"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
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

@keyframes pulse-slow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.animate-pulse-slow {
    animation: pulse-slow 5s infinite cubic-bezier(0.4, 0, 0.6, 1);
}

textarea::placeholder {
    color: rgba(107, 114, 128, 0.5);
}

/* Scroll styling */
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
