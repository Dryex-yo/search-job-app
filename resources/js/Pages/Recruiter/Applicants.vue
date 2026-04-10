<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import CVPreviewModal from '@/Components/CVPreviewModal.vue';
import CoverLetterModal from '@/Components/CoverLetterModal.vue';
import NotificationContainer from '@/Components/NotificationContainer.vue';
import RecruiterTableRowSkeleton from '@/Components/RecruiterTableRowSkeleton.vue';
import EmptyState from '@/Components/EmptyState.vue';
import RecruiterPageLayout from '@/Layouts/RecruiterPageLayout.vue';
import { useNotification } from '@/Composables/useNotification';

const page = usePage();

const props = defineProps({
    applicants: {
        type: Array,
        default: () => []
    },
    pagination: {
        type: Object,
        default: () => ({
            current_page: 1,
            total: 0,
            per_page: 20,
            has_more: false,
            next_page: 2,
        })
    }
});

// Reactive state untuk semua applicants yang telah dimuat
const allApplicants = ref([...props.applicants]);
const isLoadingMore = ref(false);
const isInitialLoading = ref(true);
const paginationData = ref({ ...props.pagination });

// State untuk CV Preview Modal
const showCVPreview = ref(false);
const selectedApplicantCV = ref(null);
const selectedApplicantName = ref('');

// State untuk Cover Letter Modal
const showCoverLetterModal = ref(false);
const selectedApplicantCoverLetter = ref('');
const selectedApplicantNameForLetter = ref('');

// State untuk export
const isExporting = ref(false);

// Intersection Observer reference
let intersectionObserver = null;
const sentinelElement = ref(null);

// Initialize notification
const { success: showSuccess, error: showError } = useNotification();

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

const updateStatus = (id, newStatus) => {
    router.patch(route('recruiter.applicants.update', id), {
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

const exportToExcel = async () => {
    try {
        isExporting.value = true;
        await new Promise(resolve => setTimeout(resolve, 300)); // Simulasi loading
        window.location.href = route('recruiter.applicants.export');
        // Reset loading state after 1 second
        setTimeout(() => {
            isExporting.value = false;
        }, 1000);
    } catch (error) {
        isExporting.value = false;
        showError('Gagal Export', 'Terjadi kesalahan saat export data. Silakan coba lagi.');
    }
};

const resetFilters = () => {
    // Reset ke halaman 1 dan reload data
    allApplicants.value = [];
    paginationData.value = {
        current_page: 1,
        total: 0,
        per_page: 20,
        has_more: false,
        next_page: 2,
    };
    router.get(route('recruiter.applicants'), {}, {
        preserveState: false,
    });
};

// Load more applicants using Intersection Observer
const loadMoreApplicants = () => {
    if (isLoadingMore.value || !paginationData.value.has_more) {
        return;
    }

    isLoadingMore.value = true;

    // Gunakan Inertia.reload dengan page parameter dan preserveScroll
    router.get(
        route('recruiter.applicants'),
        { 
            page: paginationData.value.next_page,
            per_page: paginationData.value.per_page 
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                // Data baru sudah ada di props, append ke allApplicants
                const newApplicants = page.props.applicants;
                const newPagination = page.props.pagination;
                
                allApplicants.value.push(...newApplicants);
                paginationData.value = newPagination;
                isLoadingMore.value = false;
            },
            onError: () => {
                isLoadingMore.value = false;
                showError('Gagal Memuat', 'Terjadi kesalahan saat memuat data pelamar. Silakan coba lagi.');
            }
        }
    );
};

// Setup Intersection Observer untuk infinite scroll
const setupIntersectionObserver = () => {
    if (!sentinelElement.value) return;

    const options = {
        root: null, // viewport
        rootMargin: '100px', // Trigger 100px sebelum mencapai bawah
        threshold: 0.1
    };

    intersectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && paginationData.value.has_more && !isLoadingMore.value) {
                loadMoreApplicants();
            }
        });
    }, options);

    intersectionObserver.observe(sentinelElement.value);
};

// Cleanup Intersection Observer
const cleanupIntersectionObserver = () => {
    if (intersectionObserver) {
        intersectionObserver.disconnect();
        intersectionObserver = null;
    }
};

onMounted(() => {
    // Update data dari props
    allApplicants.value = [...props.applicants];
    paginationData.value = { ...props.pagination };
    
    // Set initial loading ke false setelah component mount
    setTimeout(() => {
        isInitialLoading.value = false;
    }, 300);

    // Setup intersection observer
    setupIntersectionObserver();
});

onBeforeUnmount(() => {
    cleanupIntersectionObserver();
});
</script>

<template>
    <Head title="Dryex Recruiter - Applicants" />

    <NotificationContainer />

    <RecruiterPageLayout title="Review Applicants" subtitle="Manage candidate applications and track their progress.">
        <div class="flex justify-end mb-6">
            <button @click="exportToExcel" :disabled="isExporting"
                class="px-6 py-3 bg-gradient-to-r from-green-500/20 to-emerald-500/20 border border-green-500/50 rounded-2xl text-sm font-bold text-green-400 uppercase tracking-widest hover:from-green-500/30 hover:to-emerald-500/30 hover:border-green-500/70 disabled:opacity-60 disabled:cursor-not-allowed transition-all duration-300 flex items-center gap-2 shadow-lg shadow-green-500/10">
                <!-- Spinner yang berputar saat export -->
                <span v-if="isExporting" class="inline-block animate-spin">⏳</span>
                <span v-else>📊</span>
                {{ isExporting ? 'Mempersiapkan...' : 'Export Report' }}
            </button>
        </div>

        <div class="bg-white/[0.01] border border-white/10 rounded-[3rem] p-10 shadow-inner relative overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Position</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton Rows - Show during initial load -->
                        <RecruiterTableRowSkeleton v-for="(_, index) in Array.from({ length: 5 }, (_, i) => i)" v-show="isInitialLoading" :key="`skeleton-${index}`" />

                        <!-- Data Rows - Show when loading is complete -->
                        <tr v-for="applicant in allApplicants" v-show="!isInitialLoading" :key="applicant.id" class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-xs font-bold text-white">
                                        {{ applicant.avatar }}
                                    </div>
                                    <span class="font-semibold text-white">{{ applicant.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-300">{{ applicant.role }}</td>
                            <td class="px-6 py-4">
                                <select :value="applicant.status" @change="(e) => updateStatus(applicant.id, e.target.value.toLowerCase())"
                                    :class="['px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider border transition-all cursor-pointer', getStatusClass(applicant.status)]">
                                    <option value="pending">Pending</option>
                                    <option value="shortlisted">Shortlisted</option>
                                    <option value="interview">Interview</option>
                                    <option value="hired">Hired</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </td>
                            <td class="px-6 py-4 text-gray-400 text-sm">{{ applicant.date }}</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <button @click="() => openCVPreview(applicant)"
                                        class="px-3 py-2 rounded-lg bg-blue-500/20 border border-blue-500/50 text-blue-400 text-xs font-bold hover:bg-blue-500/30 transition-all">
                                        📄 CV
                                    </button>
                                    <button v-if="applicant.cover_letter" @click="() => openCoverLetterModal(applicant)"
                                        class="px-3 py-2 rounded-lg bg-purple-500/20 border border-purple-500/50 text-purple-400 text-xs font-bold hover:bg-purple-500/30 transition-all">
                                        📝 Letter
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Loading skeleton untuk additional items -->
                        <RecruiterTableRowSkeleton v-for="(_, index) in Array.from({ length: 3 }, (_, i) => i)" v-show="isLoadingMore" :key="`load-more-skeleton-${index}`" />
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <EmptyState 
                v-show="!isInitialLoading && allApplicants.length === 0"
                :title="'Oops! Pelamar tidak ditemukan'"
                :description="'Belum ada pelamar yang mendaftar untuk posisi ini. Tunggu sampai kandidat baru melamar atau bagikan lowongan pekerjaan ke saluran yang lebih luas.'"
                :onReset="resetFilters"
                :resetButtonText="'Refresh'"
            />

            <!-- Sentinel element untuk Intersection Observer, trigger load more -->
            <div v-show="!isInitialLoading && allApplicants.length > 0" ref="sentinelElement" class="h-1 w-full"></div>

            <!-- Loading indicator untuk end of list -->
            <div v-if="!paginationData.has_more && allApplicants.length > 0 && !isInitialLoading" class="text-center py-8 text-gray-400 text-sm">
                ✓ Semua pelamar telah dimuat ({{ paginationData.total }} total)
            </div>
        </div>

        <!-- CV Preview Modal -->
        <CVPreviewModal 
            :show="showCVPreview"
            :resume-path="selectedApplicantCV"
            :applicant-name="selectedApplicantName"
            @close="closeCVPreview"
        />

        <!-- Cover Letter Modal -->
        <CoverLetterModal 
            :show="showCoverLetterModal"
            :content="selectedApplicantCoverLetter"
            :applicant-name="selectedApplicantNameForLetter"
            @close="closeCoverLetterModal"
        />
    </RecruiterPageLayout>
</template>
