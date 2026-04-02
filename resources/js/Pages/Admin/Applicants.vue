<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import CVPreviewModal from '@/Components/CVPreviewModal.vue';
import CoverLetterModal from '@/Components/CoverLetterModal.vue';
import NotificationContainer from '@/Components/NotificationContainer.vue';
import AdminPageLayout from '@/Layouts/AdminPageLayout.vue';
import { useNotification } from '@/Composables/useNotification';

const props = defineProps({
    applicants: {
        type: Array,
        default: () => []
    }
});

// State untuk CV Preview Modal
const showCVPreview = ref(false);
const selectedApplicantCV = ref(null);
const selectedApplicantName = ref('');

// State untuk Cover Letter Modal
const showCoverLetterModal = ref(false);
const selectedApplicantCoverLetter = ref('');
const selectedApplicantNameForLetter = ref('');

// Sorting state
const sortOrder = ref('default'); // 'default', 'highest', 'lowest'

// Initialize notification
const { success: showSuccess, error: showError, info: showInfo } = useNotification();

// Computed property untuk sorted applicants
const sortedApplicants = computed(() => {
    let sorted = [...props.applicants];
    
    if (sortOrder.value === 'highest') {
        sorted.sort((a, b) => (b.ai_match_score || 0) - (a.ai_match_score || 0));
    } else if (sortOrder.value === 'lowest') {
        sorted.sort((a, b) => (a.ai_match_score || 0) - (b.ai_match_score || 0));
    }
    
    return sorted;
});

// Toggle sort order
const toggleSort = () => {
    if (sortOrder.value === 'default') {
        sortOrder.value = 'highest';
    } else if (sortOrder.value === 'highest') {
        sortOrder.value = 'lowest';
    } else {
        sortOrder.value = 'default';
    }
};

// Get sort icon
const getSortIcon = () => {
    if (sortOrder.value === 'highest') return '↓';
    if (sortOrder.value === 'lowest') return '↑';
    return '⇅';
};

// Get sort label
const getSortLabel = () => {
    if (sortOrder.value === 'highest') return 'Tertinggi';
    if (sortOrder.value === 'lowest') return 'Terendah';
    return 'Default';
};

const getStatusClass = (status) => {
    const s = status.toLowerCase();
    switch (s) {
        case 'hired': return 'text-green-400 bg-green-400/10 border-green-400/20 shadow-[0_0_15px_rgba(74,222,128,0.1)]';
        case 'interview': return 'text-cyan-400 bg-cyan-400/10 border-cyan-400/20 shadow-[0_0_15px_rgba(34,211,238,0.1)]';
        case 'shortlisted': return 'text-blue-400 bg-blue-400/10 border-blue-400/20 shadow-[0_0_15px_rgba(59,130,246,0.1)]';
        case 'pending': return 'text-yellow-400 bg-yellow-400/10 border-yellow-400/20 shadow-[0_0_15px_rgba(250,204,21,0.1)]';
        case 'rejected': return 'text-red-400 bg-red-400/10 border-red-400/20 shadow-[0_0_15px_rgba(248,113,113,0.1)]';
        default: return 'text-gray-400 bg-gray-400/10 border-gray-400/20';
    }
};

const getScoreClass = (score) => {
    if (!score) return 'text-gray-500';
    if (score >= 80) return 'text-green-400';
    if (score >= 60) return 'text-yellow-400';
    if (score >= 40) return 'text-orange-400';
    return 'text-red-400';
};

const getProgressBarClass = (score) => {
    if (!score) return 'bg-gray-600 shadow-[0_0_10px_rgba(75,85,99,0.3)]';
    if (score >= 80) return 'bg-gradient-to-r from-green-500 to-emerald-500 shadow-[0_0_20px_rgba(16,185,129,0.6)]';
    if (score >= 60) return 'bg-gradient-to-r from-yellow-500 to-yellow-400 shadow-[0_0_20px_rgba(234,179,8,0.6)]';
    if (score >= 40) return 'bg-gradient-to-r from-orange-500 to-red-400 shadow-[0_0_20px_rgba(249,115,22,0.6)]';
    return 'bg-gradient-to-r from-red-500 to-red-600 shadow-[0_0_20px_rgba(239,68,68,0.6)]';
};

const getAnalysisStatus = (status) => {
    switch (status) {
        case 'completed':
            return 'Analyzed';
        case 'analyzing':
            return 'Analyzing...';
        case 'pending':
            return 'Pending';
        case 'failed':
            return 'Failed';
        default:
            return '-';
    }
};

const updateStatus = (id, newStatus) => {
    router.patch(route('admin.applications.update', id), {
        status: newStatus
    }, {
        preserveScroll: true,
        onSuccess: () => {
            const statusLabel = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            showSuccess('Status Diperbarui', `Pelamar berhasil diubah menjadi ${statusLabel}`);
        },
        onError: (errors) => {
            showError('Gagal Memperbarui', 'Terjadi kesalahan saat memperbarui status. Silakan coba lagi.');
        }
    });
};

const openCVPreview = (applicant) => {
    selectedApplicantCV.value = applicant.resume_path;
    selectedApplicantName.value = applicant.name;
    showCVPreview.value = true;
};

const closeCVPreview = () => {
    showCVPreview.value = false;
    selectedApplicantCV.value = null;
    selectedApplicantName.value = '';
};

const openCoverLetterModal = (applicant) => {
    selectedApplicantCoverLetter.value = applicant.cover_letter || 'Tidak ada surat lamaran';
    selectedApplicantNameForLetter.value = applicant.name;
    showCoverLetterModal.value = true;
};

const closeCoverLetterModal = () => {
    showCoverLetterModal.value = false;
    selectedApplicantCoverLetter.value = '';
    selectedApplicantNameForLetter.value = '';
};

const exportToExcel = () => {
    window.location.href = route('admin.applicants.export.excel');
};
</script>

<template>
    <Head title="Dryex Admin - Applicants List" />

    <NotificationContainer />

    <AdminPageLayout title="Manage Applicants" subtitle="Review and track your job candidates efficiently.">
        <div class="flex justify-end mb-6">
            <button @click="exportToExcel"
                class="px-6 py-3 bg-gradient-to-r from-green-500/20 to-emerald-500/20 border border-green-500/50 rounded-2xl text-sm font-bold text-green-400 uppercase tracking-widest hover:from-green-500/30 hover:to-emerald-500/30 hover:border-green-500/70 transition-all duration-300 flex items-center gap-2 shadow-lg shadow-green-500/10">
                <span>📊</span>
                Export Report
            </button>
        </div>

        <div class="bg-white/[0.01] border border-white/10 rounded-[3rem] p-10 shadow-inner glass-grain relative overflow-hidden">
            <!-- Scroll Indicator (Top) -->
            <div class="mb-3 flex items-center justify-between px-2">
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Geser ke kanan untuk melihat lebih banyak →</span>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-500">{{ applicants.length }} Pelamar</span>
                    <div class="text-[10px] font-semibold text-cyan-400 bg-cyan-400/20 px-2 py-1 rounded border border-cyan-400/30 cursor-pointer hover:bg-cyan-400/30 transition-all" @click="toggleSort" title="Klik untuk mengubah urutan">
                        {{ getSortIcon() }} {{ getSortLabel() }}
                    </div>
                </div>
            </div>

            <!-- Horizontal Scrollable Container -->
            <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-cyan-500/50 scrollbar-track-white/5 rounded-2xl">
                <table class="w-full text-left border-separate border-spacing-y-2 min-w-max">
                    <thead>
                        <tr class="text-[10px] font-black text-gray-700 dark:text-gray-400 uppercase tracking-[0.4em] italic sticky top-0 bg-white/5 backdrop-blur-sm">
                            <th class="pb-8 px-6 whitespace-nowrap">Candidate</th>
                            <th class="pb-8 px-6 whitespace-nowrap">Applied Role</th>
                            <th class="pb-8 px-6 whitespace-nowrap">Status</th>
                            <th class="pb-8 px-6 whitespace-nowrap cursor-pointer group hover:text-cyan-400 transition-colors" @click="toggleSort" title="Klik untuk mengurutkan Match Score">
                                <div class="flex items-center gap-2">
                                    <span>Match Score</span>
                                    <span class="text-cyan-400 opacity-0 group-hover:opacity-100 transition-opacity">{{ getSortIcon() }}</span>
                                </div>
                            </th>
                            <th class="pb-8 px-6 whitespace-nowrap text-right">Date Applied</th>
                            <th class="pb-8 px-6 whitespace-nowrap text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="applicant in sortedApplicants" :key="applicant.id" class="group hover:bg-white/[0.02] transition-all duration-300 cursor-pointer">
                            <td class="py-7 px-6 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center font-bold text-white text-sm flex-shrink-0">
                                        {{ applicant.name?.charAt(0) || 'A' }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-white text-sm truncate">{{ applicant.name }}</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ applicant.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-7 px-6 whitespace-nowrap">
                                <p class="font-semibold text-white text-sm truncate">{{ applicant.job?.title || 'N/A' }}</p>
                            </td>
                            <td class="py-7 px-6 whitespace-nowrap">
                                <span :class="getStatusClass(applicant.status)" class="px-3 py-1 rounded-full text-xs font-bold border inline-block">
                                    {{ applicant.status?.charAt(0).toUpperCase() + applicant.status?.slice(1) }}
                                </span>
                            </td>
                            <td class="py-7 px-6 whitespace-nowrap">
                                <div v-if="applicant.ai_match_score !== null && applicant.ai_match_score !== undefined" class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span :class="[getScoreClass(applicant.ai_match_score), 'font-bold text-lg']">
                                            {{ applicant.ai_match_score }}
                                        </span>
                                        <span class="text-gray-500 text-sm">/100</span>
                                    </div>
                                    <div class="w-24 bg-gray-700/50 rounded-full h-1.5 overflow-hidden border border-gray-600/50">
                                        <div 
                                            :class="getProgressBarClass(applicant.ai_match_score)"
                                            :style="{ width: applicant.ai_match_score + '%' }"
                                            class="h-full transition-all duration-300"
                                        />
                                    </div>
                                    <p class="text-xs text-gray-500">{{ getAnalysisStatus(applicant.ai_analysis_status) }}</p>
                                </div>
                                <div v-else-if="applicant.ai_analysis_status === 'analyzing'" class="flex items-center gap-2">
                                    <div class="w-4 h-4 border-2 border-cyan-500 border-t-transparent rounded-full animate-spin"></div>
                                    <span class="text-xs text-cyan-400">Analyzing...</span>
                                </div>
                                <div v-else class="text-xs text-gray-500">
                                    {{ getAnalysisStatus(applicant.ai_analysis_status) }}
                                </div>
                            </td>
                            <td class="py-7 px-6 whitespace-nowrap text-right">
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ new Date(applicant.created_at).toLocaleDateString() }}</p>
                            </td>
                            <td class="py-7 px-6 whitespace-nowrap text-right">
                                <div class="flex gap-2 justify-end flex-wrap">
                                    <button @click="openCVPreview(applicant)"
                                        class="px-3 py-1.5 bg-blue-500/20 hover:bg-blue-500/40 border border-blue-500/50 rounded-lg text-xs font-bold text-blue-400 transition-all flex-shrink-0">
                                        📄 CV
                                    </button>
                                    <button @click="openCoverLetterModal(applicant)"
                                        class="px-3 py-1.5 bg-purple-500/20 hover:bg-purple-500/40 border border-purple-500/50 rounded-lg text-xs font-bold text-purple-400 transition-all flex-shrink-0">
                                        💬 Surat
                                    </button>
                                    <select @change="updateStatus(applicant.id, $event.target.value)" 
                                        :value="applicant.status"
                                        class="px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg text-xs font-bold text-black cursor-pointer hover:bg-white/20 transition-all flex-shrink-0">
                                        <option value="pending">Pending</option>
                                        <option value="interview">Interview</option>
                                        <option value="shortlisted">Shortlisted</option>
                                        <option value="hired">Hired</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="applicants.length === 0" class="text-center py-12">
                    <p class="text-gray-600 dark:text-gray-400 text-sm">No applicants found</p>
                </div>
            </div>
        </div>

        <CVPreviewModal 
            :show="showCVPreview"
            :cv-path="selectedApplicantCV"
            :candidate-name="selectedApplicantName"
            @close="closeCVPreview"
        />

        <CoverLetterModal
            v-if="showCoverLetterModal"
            :show="showCoverLetterModal"
            :cover-letter="selectedApplicantCoverLetter"
            :candidate-name="selectedApplicantNameForLetter"
            title="Lihat Surat Lamaran"
            @close="closeCoverLetterModal"
        />
    </AdminPageLayout>
</template>

<style scoped>
/* Custom scrollbar untuk horizontal scroll */
.scrollbar-thin::-webkit-scrollbar {
    height: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 0.5rem;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(34, 211, 238, 0.5);
    border-radius: 0.5rem;
    transition: background-color 0.3s ease;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(34, 211, 238, 0.8);
}

/* Firefox scrollbar */
.scrollbar-thin {
    scrollbar-color: rgba(34, 211, 238, 0.5) rgba(255, 255, 255, 0.05);
    scrollbar-width: thin;
}

/* Smooth scrolling */
.scrollbar-thin {
    scroll-behavior: smooth;
}

/* Fade effect untuk menunjukkan ada konten tersembunyi */
@supports (backdrop-filter: blur(1px)) {
    .scrollbar-thin::after {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 40px;
        background: linear-gradient(90deg, transparent, rgba(8, 11, 20, 0.3));
        pointer-events: none;
        z-index: 10;
    }
}
</style>
