<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import CVPreviewModal from '@/Components/CVPreviewModal.vue';
import CoverLetterModal from '@/Components/CoverLetterModal.vue';
import NotificationContainer from '@/Components/NotificationContainer.vue';
import AdminPageLayout from '@/Layouts/AdminPageLayout.vue';
import MatchScoreDisplay from '@/Components/MatchScoreDisplay.vue';
import TableRowSkeleton from '@/Components/TableRowSkeleton.vue';
import { useNotification } from '@/Composables/useNotification';

const props = defineProps({
    applicants: {
        type: Object,  // Changed from Array to Object (pagination object)
        default: () => ({
            data: [],
            current_page: 1,
            per_page: 15,
            total: 0,
            last_page: 1,
        })
    },
    filters: {
        type: Object,
        default: () => ({})
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

// Filter state
const filterForm = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    score_min: props.filters?.score_min || '',
    score_max: props.filters?.score_max || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    sort: props.filters?.sort || 'latest',
    per_page: props.filters?.per_page || 15,
});

const showFilters = ref(false);

// Loading state untuk skeleton
const isLoading = ref(true);
const skeletonRows = ref(Array.from({ length: 5 }, (_, i) => i));

// Initialize notification
const { success: showSuccess, error: showError, info: showInfo } = useNotification();

// Apply filters
const applyFilters = () => {
    router.get(route('admin.applicants'), filterForm.value, {
        preserveState: false,
    });
};

// Reset filters
const resetFilters = () => {
    filterForm.value = {
        search: '',
        status: '',
        score_min: '',
        score_max: '',
        date_from: '',
        date_to: '',
        sort: 'latest',
    };
    router.get(route('admin.applicants'), {}, {
        preserveState: false,
    });
};

// Check if any filter is active
const hasActiveFilters = computed(() => {
    return filterForm.value.search || 
           filterForm.value.status || 
           filterForm.value.score_min || 
           filterForm.value.score_max ||
           filterForm.value.date_from ||
           filterForm.value.date_to;
});

// Pagination computed
const applicantsList = computed(() => props.applicants?.data || []);
const currentPage = computed(() => props.applicants?.current_page || 1);
const perPage = computed(() => props.applicants?.per_page || 15);
const totalApplicants = computed(() => props.applicants?.total || 0);
const lastPage = computed(() => props.applicants?.last_page || 1);
const startRecord = computed(() => (currentPage.value - 1) * perPage.value + 1);
const endRecord = computed(() => Math.min(currentPage.value * perPage.value, totalApplicants.value));

// Generate page numbers for pagination
const pageNumbers = computed(() => {
    const pages = [];
    const maxPages = 5; // Show 5 page numbers max
    let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
    let end = Math.min(lastPage.value, start + maxPages - 1);
    
    if (end - start < maxPages - 1) {
        start = Math.max(1, end - maxPages + 1);
    }
    
    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    return pages;
});

// Pagination methods
const goToPage = (page) => {
    router.get(route('admin.applicants'), {
        ...filterForm.value,
        page: page
    }, {
        preserveState: false,
    });
};

const nextPage = () => {
    if (currentPage.value < lastPage.value) {
        goToPage(currentPage.value + 1);
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        goToPage(currentPage.value - 1);
    }
};

// Change per page
const changePerPage = () => {
    router.get(route('admin.applicants'), {
        ...filterForm.value,
        per_page: filterForm.value.per_page,
        page: 1
    }, {
        preserveState: false,
    });
};

// Computed property untuk sorted applicants (client-side additional sorting)
const sortedApplicants = computed(() => {
    let sorted = [...applicantsList.value];
    
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

// Set loading state to false after component mounts (since data is already available)
onMounted(() => {
    // Simulate brief loading state for skeleton animation effect (optional: adjust timing as needed)
    // Remove setTimeout if immediate load is preferred
    setTimeout(() => {
        isLoading.value = false;
    }, 300);
});

// Also watch for applicants data changes to disable loading
watch(() => props.applicants?.data?.length, (newLength) => {
    if (newLength > 0) {
        isLoading.value = false;
    }
});
</script>

<template>
    <Head title="Dryex Admin - Applicants List" />

    <NotificationContainer />

    <AdminPageLayout title="Manage Applicants" subtitle="Review and track your job candidates efficiently.">
        <!-- Action Buttons -->
        <div class="flex justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-2">
                <button @click="showFilters = !showFilters"
                    class="px-4 py-2 bg-cyan-500/20 hover:bg-cyan-500/40 border border-cyan-500/50 rounded-2xl text-sm font-bold text-cyan-400 uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <span>🔍</span>
                    {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                </button>
                <span v-if="hasActiveFilters" class="text-xs bg-cyan-500/30 text-cyan-300 px-3 py-1 rounded-full border border-cyan-500/50">
                    Active Filters
                </span>
            </div>
            <button @click="exportToExcel"
                class="px-6 py-3 bg-gradient-to-r from-green-500/20 to-emerald-500/20 border border-green-500/50 rounded-2xl text-sm font-bold text-green-400 uppercase tracking-widest hover:from-green-500/30 hover:to-emerald-500/30 hover:border-green-500/70 transition-all duration-300 flex items-center gap-2 shadow-lg shadow-green-500/10">
                <span>📊</span>
                Export Report
            </button>
        </div>

        <!-- Filter Panel -->
        <div v-show="showFilters" class="bg-white/[0.01] border border-white/10 rounded-[2rem] p-8 mb-6 shadow-inner">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-400 uppercase tracking-wider mb-2">Search</label>
                    <input 
                        v-model="filterForm.search" 
                        type="text" 
                        placeholder="Name, email, or job title..."
                        @keyup.enter="applyFilters"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500/50" 
                    />
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-400 uppercase tracking-wider mb-2">Status</label>
                    <select 
                        v-model="filterForm.status"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-cyan-500/50"
                    >
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="interview">Interview</option>
                        <option value="shortlisted">Shortlisted</option>
                        <option value="hired">Hired</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <!-- Score Min -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-400 uppercase tracking-wider mb-2">Min Score</label>
                    <input 
                        v-model.number="filterForm.score_min" 
                        type="number" 
                        min="0" 
                        max="100"
                        placeholder="0"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500/50" 
                    />
                </div>

                <!-- Score Max -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-400 uppercase tracking-wider mb-2">Max Score</label>
                    <input 
                        v-model.number="filterForm.score_max" 
                        type="number" 
                        min="0" 
                        max="100"
                        placeholder="100"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500/50" 
                    />
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-400 uppercase tracking-wider mb-2">From Date</label>
                    <input 
                        v-model="filterForm.date_from" 
                        type="date"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-cyan-500/50" 
                    />
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-400 uppercase tracking-wider mb-2">To Date</label>
                    <input 
                        v-model="filterForm.date_to" 
                        type="date"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-cyan-500/50" 
                    />
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex gap-3 mt-6 justify-end">
                <button 
                    @click="resetFilters"
                    class="px-6 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-sm font-bold text-white uppercase tracking-widest transition-all"
                >
                    Reset
                </button>
                <button 
                    @click="applyFilters"
                    class="px-6 py-2 bg-cyan-500 hover:bg-cyan-600 border border-cyan-500 rounded-xl text-sm font-bold text-slate-900 uppercase tracking-widest transition-all"
                >
                    Apply Filters
                </button>
            </div>

            <!-- Per Page Selector -->
            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-white/10">
                <label class="text-xs font-bold text-gray-700 dark:text-gray-400 uppercase tracking-wider">Show per page:</label>
                <select 
                    v-model.number="filterForm.per_page"
                    @change="changePerPage"
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-cyan-500/50 text-sm"
                >
                    <option :value="15">15 items</option>
                    <option :value="25">25 items</option>
                    <option :value="50">50 items</option>
                    <option :value="100">100 items</option>
                </select>
            </div>
        </div>

        <div class="bg-white/[0.01] border border-white/10 rounded-[3rem] p-10 shadow-inner glass-grain relative overflow-hidden">
            <!-- Scroll Indicator (Top) -->
            <div class="mb-3 flex items-center justify-between px-2">
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Geser ke kanan untuk melihat lebih banyak →</span>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-500">
                        Showing {{ startRecord }} - {{ endRecord }} of {{ totalApplicants }} Pelamar
                    </span>
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
                        <!-- Skeleton Rows - Show during initial load -->
                        <TableRowSkeleton v-for="(_, index) in skeletonRows" v-show="isLoading" :key="`skeleton-${index}`" />

                        <!-- Data Rows - Show when loading is complete -->
                        <tr v-for="applicant in sortedApplicants" v-show="!isLoading" :key="applicant.id" class="group hover:bg-white/[0.02] transition-all duration-300 cursor-pointer">
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
                                <MatchScoreDisplay
                                    :score="applicant.ai_match_score"
                                    :analysis-status="applicant.ai_analysis_status || 'pending'"
                                />
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
                                        class="px-3 py-1.5 bg-white/10 border border-black/20 rounded-lg text-xs font-bold text-white cursor-pointer hover:bg-black/20 transition-all flex-shrink-0">
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

                <div v-show="!isLoading && applicantsList.length === 0" class="text-center py-12">
                    <p class="text-gray-600 dark:text-gray-400 text-sm">No applicants found</p>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div v-if="totalApplicants > perPage" class="mt-8 flex items-center justify-between">
                <!-- Page Info -->
                <div class="text-sm text-gray-500">
                    Page <span class="font-bold text-white">{{ currentPage }}</span> of <span class="font-bold text-white">{{ lastPage }}</span>
                </div>

                <!-- Pagination Buttons -->
                <div class="flex items-center gap-2">
                    <!-- Previous Button -->
                    <button 
                        @click="prevPage"
                        :disabled="currentPage === 1"
                        class="px-4 py-2 bg-white/10 hover:bg-white/20 disabled:bg-white/5 disabled:opacity-50 border border-white/20 rounded-lg text-sm font-bold text-white uppercase tracking-wider transition-all disabled:cursor-not-allowed"
                    >
                        ← Prev
                    </button>

                    <!-- Page Numbers -->
                    <div class="flex gap-1">
                        <!-- First Page -->
                        <button 
                            v-if="pageNumbers[0] > 1"
                            @click="goToPage(1)"
                            class="px-3 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-sm font-bold text-white transition-all"
                        >
                            1
                        </button>

                        <!-- Ellipsis -->
                        <span v-if="pageNumbers[0] > 2" class="px-2 py-2 text-gray-500">...</span>

                        <!-- Page Number Buttons -->
                        <button 
                            v-for="page in pageNumbers" 
                            :key="page"
                            @click="goToPage(page)"
                            :class="[
                                'px-3 py-2 rounded-lg text-sm font-bold transition-all',
                                currentPage === page 
                                    ? 'bg-cyan-500 text-slate-900 border border-cyan-500' 
                                    : 'bg-white/10 hover:bg-white/20 border border-white/20 text-white'
                            ]"
                        >
                            {{ page }}
                        </button>

                        <!-- Ellipsis -->
                        <span v-if="pageNumbers[pageNumbers.length - 1] < lastPage - 1" class="px-2 py-2 text-gray-500">...</span>

                        <!-- Last Page -->
                        <button 
                            v-if="pageNumbers[pageNumbers.length - 1] < lastPage"
                            @click="goToPage(lastPage)"
                            class="px-3 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-sm font-bold text-white transition-all"
                        >
                            {{ lastPage }}
                        </button>
                    </div>

                    <!-- Next Button -->
                    <button 
                        @click="nextPage"
                        :disabled="currentPage === lastPage"
                        class="px-4 py-2 bg-white/10 hover:bg-white/20 disabled:bg-white/5 disabled:opacity-50 border border-white/20 rounded-lg text-sm font-bold text-white uppercase tracking-wider transition-all disabled:cursor-not-allowed"
                    >
                        Next →
                    </button>
                </div>

                <!-- Showing Info -->
                <div class="text-sm text-gray-500 text-right">
                    <span class="font-bold text-white">{{ startRecord }}-{{ endRecord }}</span> of <span class="font-bold text-white">{{ totalApplicants }}</span>
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
