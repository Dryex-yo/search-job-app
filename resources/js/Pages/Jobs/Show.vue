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
    },
    profileCompletion: {
        type: Number,
        default: 0,
    }
});

// Initialize notification
const { success: showSuccess, error: showError, warning: showWarning } = useNotification();

// Auth check
const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
const currentUser = computed(() => page.props.auth?.user);
const isProfileComplete = computed(() => props.profileCompletion === 100);
const profileCompletionText = computed(() => {
    if (!isAuthenticated.value) return 'Login diperlukan';
    if (isProfileComplete.value) return 'Profile Anda 100% Lengkap ✓';
    return `Profile Anda ${props.profileCompletion}% Lengkap - Selesaikan untuk Apply`;
});

// State
const cvFile = ref(null);
const cvFileName = ref('');
const isSubmitting = ref(false);
const showPreview = ref(false);
const userProfileCV = computed(() => currentUser.value?.resume_path);

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

    // Check if profile is complete (100%)
    if (!isProfileComplete.value) {
        showError(
            'Profile Belum Lengkap!', 
            `Profil Anda baru ${props.profileCompletion}% lengkap. Silakan lengkapi semua field profil Anda hingga 100% sebelum melamar pekerjaan. Klik "Edit Profile" untuk melengkapi.`
        );
        return;
    }

    // Check if user has CV in profile or has selected a file
    if (!cvFile.value && !userProfileCV.value) {
        showError('CV Diperlukan', 'Silakan upload CV Anda di profile terlebih dahulu atau pilih file CV sekarang.');
        return;
    }

    if (!applyForm.cover_letter.trim()) {
        showWarning('Surat Lamaran Kosong', 'Sebaiknya Anda menulis surat lamaran untuk meningkatkan peluang.');
    }

    isSubmitting.value = true;
    
    // Use uploaded file if available, otherwise use profile CV
    if (cvFile.value) {
        applyForm.resume = cvFile.value;
    }

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
    
    // Convert to string if it's a number
    let salaryStr = String(salary).trim();
    
    // Remove existing "Rp" prefix if any
    if (salaryStr.startsWith('Rp')) {
        salaryStr = salaryStr.replace(/^Rp\s*/i, '').trim();
    }
    
    // Try to parse as number and format
    const salaryNum = parseInt(salaryStr.replace(/\D/g, ''), 10);
    if (!isNaN(salaryNum)) {
        return 'Rp ' + salaryNum.toLocaleString('id-ID');
    }
    
    // If not a number, return as is with Rp prefix
    return salaryStr || 'Sesuai Kesepakatan';
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

        <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-[#080B14] dark:to-[#0f1422] text-slate-900 dark:text-white selection:bg-cyan-500/30 dark:selection:bg-cyan-500/30 p-4 md:p-8 transition-colors duration-300">
            
            <!-- Background Effects -->
            <div class="fixed inset-0 opacity-[0.02] dark:opacity-[0.03] pointer-events-none grain-bg"></div>
            <div class="absolute top-[-10%] left-[-5%] w-[600px] h-[600px] bg-cyan-500/5 dark:bg-cyan-500/10 blur-[130px] rounded-full animate-pulse-slow"></div>
            <div class="absolute bottom-[-10%] right-[-5%] w-[700px] h-[700px] bg-blue-600/5 dark:bg-blue-600/10 blur-[150px] rounded-full animate-pulse-slow"></div>

            <div class="relative z-10 max-w-4xl mx-auto">
                
                <!-- Header with Back Link -->
                <div class="mb-8 flex items-center justify-between">
                    <Link 
                        href="/"
                        class="inline-flex items-center gap-2 text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 transition-colors font-semibold"
                    >
                        <span>←</span>
                        <span>Kembali ke Daftar Lowongan</span>
                    </Link>
                </div>

                <!-- Main Content Card -->
                <div class="bg-white dark:bg-white/[0.005] backdrop-blur-[60px] border border-slate-200 dark:border-white/20 rounded-[2rem] shadow-lg dark:shadow-[0_40px_100px_rgba(0,0,0,0.8)] overflow-hidden transition-colors duration-300">
                    
                    <!-- Job Header Section -->
                    <div class="border-b border-slate-200 dark:border-white/10 p-8 bg-gradient-to-r from-transparent dark:from-white/[0.01] to-transparent transition-colors duration-300">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-6">
                            <div>
                                <h1 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white mb-3 transition-colors duration-300">{{ job.title }}</h1>
                                <p class="text-lg text-slate-600 dark:text-gray-300 mb-4 transition-colors duration-300">{{ job.company_name }}</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="px-4 py-2 bg-cyan-50 dark:bg-cyan-500/20 border border-cyan-200 dark:border-cyan-400/30 text-cyan-600 dark:text-cyan-400 rounded-full text-sm font-bold transition-colors duration-300">
                                        {{ job.type }}
                                    </span>
                                    <span class="px-4 py-2 bg-blue-50 dark:bg-blue-500/20 border border-blue-200 dark:border-blue-400/30 text-blue-600 dark:text-blue-400 rounded-full text-sm font-bold transition-colors duration-300">
                                        📍 {{ job.location }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-widest text-slate-500 dark:text-gray-500 mb-2 transition-colors duration-300">Gaji</p>
                                <p class="text-2xl font-bold text-cyan-600 dark:text-cyan-400 transition-colors duration-300">{{ formatSalary(job.salary) }}</p>
                            </div>
                        </div>

                        <!-- Meta Information -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-slate-200 dark:border-white/10 transition-colors duration-300">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-slate-500 dark:text-gray-500 mb-1 transition-colors duration-300">Jenis Pekerjaan</p>
                                <p class="font-semibold text-slate-900 dark:text-white transition-colors duration-300">{{ job.type }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest text-slate-500 dark:text-gray-500 mb-1 transition-colors duration-300">Lokasi</p>
                                <p class="font-semibold text-slate-900 dark:text-white transition-colors duration-300">{{ job.location }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest text-slate-500 dark:text-gray-500 mb-1 transition-colors duration-300">Dibuat</p>
                                <p class="font-semibold text-slate-900 dark:text-white transition-colors duration-300">{{ formatDate(job.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest text-slate-500 dark:text-gray-500 mb-1 transition-colors duration-300">Status</p>
                                <p class="font-semibold transition-colors duration-300" :class="job.status === 'active' ? 'text-green-600 dark:text-green-400' : 'text-slate-400 dark:text-gray-400'">
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
                            <div class="bg-white dark:bg-white/[0.003] border border-slate-200 dark:border-white/10 rounded-2xl p-6 transition-colors duration-300">
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2 transition-colors duration-300">
                                    <span>📋</span> Deskripsi Lowongan
                                </h2>
                                <p class="text-slate-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap transition-colors duration-300">{{ job.description }}</p>
                            </div>
                        </div>

                        <!-- Apply Form (Right Side) -->
                        <div class="lg:col-span-1">
                            <div class="bg-white dark:bg-white/[0.003] border border-slate-200 dark:border-white/10 rounded-2xl p-6 sticky top-8 space-y-4 transition-colors duration-300">
                                
                                <!-- Login Required Notice -->
                                <div v-if="!isAuthenticated" class="bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-400/30 rounded-lg p-4 text-sm transition-colors duration-300">
                                    <p class="text-yellow-700 dark:text-yellow-400 font-semibold mb-2 transition-colors duration-300">⚠️ Login Diperlukan</p>
                                    <p class="text-yellow-600 dark:text-yellow-300 text-xs mb-3 transition-colors duration-300">Silakan login atau daftar terlebih dahulu untuk melamar lowongan ini.</p>
                                    <Link 
                                        href="/login"
                                        class="block w-full px-4 py-2 bg-yellow-100 dark:bg-yellow-500/30 border border-yellow-300 dark:border-yellow-400/50 text-yellow-700 dark:text-yellow-400 rounded-lg text-center font-bold hover:bg-yellow-200 dark:hover:bg-yellow-500/40 transition-colors mb-2"
                                    >
                                        Login
                                    </Link>
                                    <Link 
                                        href="/register"
                                        class="block w-full px-4 py-2 bg-slate-100 dark:bg-white/10 border border-slate-300 dark:border-white/20 text-slate-900 dark:text-white rounded-lg text-center font-bold hover:bg-slate-200 dark:hover:bg-white/20 transition-colors"
                                    >
                                        Daftar
                                    </Link>
                                </div>

                                <!-- Application Form -->
                                <template v-else>
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white transition-colors duration-300">Kirim Lamaran</h3>

                                    <!-- Profile Completion Status -->
                                    <div class="bg-white dark:bg-white/[0.003] border border-slate-200 dark:border-white/10 rounded-lg p-4 transition-colors duration-300">
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-sm font-semibold text-slate-700 dark:text-gray-300 transition-colors duration-300">
                                                Status Profile
                                            </p>
                                            <span :class="isProfileComplete ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400'" class="text-sm font-bold transition-colors duration-300">
                                                {{ props.profileCompletion }}%
                                            </span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-200 dark:bg-white/10 rounded-full overflow-hidden">
                                            <div 
                                                :style="{ width: props.profileCompletion + '%' }"
                                                :class="isProfileComplete ? 'bg-green-500' : 'bg-orange-500'"
                                                class="h-full transition-all duration-300"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- Profile Not Complete Warning -->
                                    <div v-if="!isProfileComplete" class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-400/30 rounded-lg p-4 transition-colors duration-300">
                                        <p class="text-sm font-semibold text-red-600 dark:text-red-400 flex items-center gap-2 mb-2 transition-colors duration-300">
                                            <span>⚠️</span> Profile Belum Lengkap
                                        </p>
                                        <p class="text-xs text-red-700 dark:text-red-300 mb-3 transition-colors duration-300">
                                            Profil Anda hanya {{ props.profileCompletion }}% lengkap. Silakan lengkapi semua informasi di profil Anda untuk dapat melamar pekerjaan.
                                        </p>
                                        <Link 
                                            href="/profile"
                                            class="inline-block w-full px-4 py-2 bg-red-100 dark:bg-red-500/30 border border-red-300 dark:border-red-400/50 text-red-700 dark:text-red-400 rounded-lg text-center font-bold hover:bg-red-200 dark:hover:bg-red-500/40 transition-colors text-sm"
                                        >
                                            → Edit Profile Sekarang
                                        </Link>
                                    </div>

                                    <!-- Profile Complete Success -->
                                    <div v-else class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-400/30 rounded-lg p-4 transition-colors duration-300">
                                        <p class="text-sm font-semibold text-green-600 dark:text-green-400 flex items-center gap-2 transition-colors duration-300">
                                            <span>✓</span> Profile Lengkap 100%
                                        </p>
                                    </div>

                                    <!-- CV Info Section -->
                                    <div v-if="userProfileCV" class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-400/30 rounded-lg p-4 transition-colors duration-300">
                                        <p class="text-sm font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-2 mb-2 transition-colors duration-300">
                                            <span>✓</span> CV dari Profile Tersedia
                                        </p>
                                        <p class="text-xs text-blue-700 dark:text-blue-300 transition-colors duration-300">
                                            Kami akan menggunakan CV yang sudah Anda upload di profil. Untuk mengubah CV, silakan update di halaman profile Anda.
                                        </p>
                                    </div>

                                    <!-- CV Upload Section -->
                                    <div class="space-y-3">
                                        <label class="text-sm font-semibold text-slate-700 dark:text-gray-300 flex items-center gap-1 transition-colors duration-300">
                                            <span>📄</span> Upload CV Baru (Opsional)
                                            <span v-if="!userProfileCV" class="text-red-400">*</span>
                                        </label>
                                        <p class="text-xs text-slate-500 dark:text-gray-500 transition-colors duration-300">
                                            {{ userProfileCV ? 'Abaikan bagian ini jika ingin menggunakan CV dari profile' : 'Silakan upload CV Anda' }}
                                        </p>
                                        
                                        <div v-if="!cvFile" class="border-2 border-dashed border-cyan-300 dark:border-cyan-400/50 rounded-lg p-4 text-center cursor-pointer hover:border-cyan-400 dark:hover:border-cyan-300 transition-colors"
                                             @click="() => document.getElementById('cv-upload')?.click()">
                                            <p class="text-sm text-slate-600 dark:text-gray-400 mb-2 transition-colors duration-300">📁 Drag & drop atau klik untuk memilih</p>
                                            <p class="text-xs text-slate-500 dark:text-gray-500 transition-colors duration-300">PDF, Max 2MB</p>
                                            <input 
                                                id="cv-upload"
                                                type="file" 
                                                accept=".pdf"
                                                @change="handleCVSelect"
                                                class="hidden"
                                            />
                                        </div>

                                        <!-- CV Selected State -->
                                        <div v-else class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-400/30 rounded-lg p-4 transition-colors duration-300">
                                            <div class="flex items-start justify-between gap-3 mb-2">
                                                <div class="flex-grow">
                                                    <p class="text-sm font-semibold text-green-600 dark:text-green-400 flex items-center gap-2 transition-colors duration-300">
                                                        <span>✓</span> File Dipilih
                                                    </p>
                                                    <p class="text-xs text-green-700 dark:text-green-300 mt-1 break-all transition-colors duration-300">{{ cvFileName }}</p>
                                                    <p class="text-xs text-green-700 dark:text-green-300 mt-1 transition-colors duration-300">{{ (cvFile.size / 1024).toFixed(2) }} KB</p>
                                                </div>
                                                <button 
                                                    type="button"
                                                    @click="clearCV"
                                                    class="text-green-600 dark:text-green-400 hover:text-red-600 dark:hover:text-red-400 transition-colors text-lg"
                                                >
                                                    ✕
                                                </button>
                                            </div>
                                            <button 
                                                type="button"
                                                @click="previewCV"
                                                class="w-full mt-2 px-3 py-1 bg-slate-100 dark:bg-white/10 border border-slate-300 dark:border-white/20 text-xs rounded text-slate-900 dark:text-white hover:bg-slate-200 dark:hover:bg-white/20 transition-colors"
                                            >
                                                👁️ Preview
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Cover Letter Section -->
                                    <div class="space-y-3">
                                        <label class="text-sm font-semibold text-slate-700 dark:text-gray-300 flex items-center gap-1 transition-colors duration-300">
                                            <span>💬</span> Surat Lamaran
                                            <span class="text-slate-500 dark:text-gray-500 transition-colors duration-300">(Opsional)</span>
                                        </label>
                                        <textarea 
                                            v-model="applyForm.cover_letter"
                                            placeholder="Ceritakan mengapa Anda tertarik dengan posisi ini..."
                                            class="w-full px-4 py-3 bg-white dark:bg-white/[0.005] border border-slate-300 dark:border-white/10 rounded-lg text-slate-900 dark:text-white text-sm placeholder-slate-400 dark:placeholder-gray-600 focus:border-cyan-500 dark:focus:border-cyan-400 focus:outline-none transition-colors resize-none"
                                            rows="5"
                                        ></textarea>
                                    </div>

                                    <!-- Submit Button -->
                                    <button 
                                        type="button"
                                        @click="submitApplication"
                                        :disabled="isSubmitting || (!cvFile && !userProfileCV) || !isProfileComplete"
                                        class="w-full px-6 py-3 font-bold rounded-lg transition-all"
                                        :class="isProfileComplete 
                                            ? 'bg-cyan-100 dark:bg-cyan-500/30 border border-cyan-300 dark:border-cyan-400/50 text-cyan-700 dark:text-cyan-400 hover:bg-cyan-200 dark:hover:bg-cyan-500/40 disabled:opacity-50 disabled:cursor-not-allowed'
                                            : 'bg-gray-100 dark:bg-gray-500/20 border border-gray-300 dark:border-gray-400/30 text-gray-500 dark:text-gray-400 cursor-not-allowed opacity-50'"
                                    >
                                        <span v-if="!isSubmitting">
                                            <span v-if="isProfileComplete">✓ Kirim Lamaran</span>
                                            <span v-else>⏳ Lengkapi Profile ({{ props.profileCompletion }}%)</span>
                                        </span>
                                        <span v-else>⏳ Mengirim...</span>
                                    </button>

                                    <!-- Info Text -->
                                    <p class="text-xs text-slate-500 dark:text-gray-500 text-center transition-colors duration-300">
                                        {{ isProfileComplete 
                                            ? 'Dengan mengirim lamaran, Anda menerima Kebijakan Privasi kami.' 
                                            : 'Lengkapi profil Anda untuk dapat melamar pekerjaan' 
                                        }}
                                    </p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PDF Preview Modal -->
            <div v-if="showPreview" class="fixed inset-0 bg-black/50 dark:bg-black/80 backdrop-blur z-50 flex items-center justify-center p-4 transition-colors duration-300" @click="showPreview = false">
                <div class="bg-white dark:bg-[#080B14] border border-slate-300 dark:border-white/20 rounded-2xl max-w-3xl w-full max-h-[85vh] flex flex-col transition-colors duration-300" @click.stop>
                    <div class="border-b border-slate-200 dark:border-white/10 p-6 flex items-center justify-between transition-colors duration-300">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white transition-colors duration-300">Preview CV</h3>
                        <button @click="showPreview = false" class="text-slate-400 dark:text-gray-400 hover:text-slate-600 dark:hover:text-white transition-colors">✕</button>
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
