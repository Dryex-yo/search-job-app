<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminPageLayout from '@/Layouts/AdminPageLayout.vue';

const props = defineProps({
    logs: {
        type: Array,
        default: () => []
    },
    pagination: {
        type: Object,
        default: () => ({
            current_page: 1,
            total: 0,
            per_page: 15,
            last_page: 1
        })
    }
});

// State
const selectedLog = ref(null);
const showDetailModal = ref(false);
const searchQuery = ref('');
const filterType = ref('all');
const perPageLogs = ref(props.pagination?.per_page || 15);

// Computed
const currentPage = computed(() => props.pagination?.current_page || 1);
const perPage = computed(() => props.pagination?.per_page || 15);
const totalLogs = computed(() => props.pagination?.total || 0);
const lastPage = computed(() => props.pagination?.last_page || 1);

const logsList = computed(() => {
    // If logs is an array directly
    if (Array.isArray(props.logs)) {
        return props.logs;
    }
    // If logs is paginated object with data property
    return props.logs?.data || [];
});

const filteredLogs = computed(() => {
    return logsList.value.filter(log => {
        const matchesSearch = !searchQuery.value || 
            log.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.causer_name.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesFilter = filterType.value === 'all' || log.subject_type === filterType.value;
        
        return matchesSearch && matchesFilter;
    });
});

const pageNumbers = computed(() => {
    const pages = [];
    const maxPages = 5;
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

const startRecord = computed(() => {
    return (currentPage.value - 1) * perPage.value + 1;
});

const endRecord = computed(() => {
    return Math.min(currentPage.value * perPage.value, totalLogs.value);
});

// Get action badge color
const getActionColor = (event) => {
    switch (event) {
        case 'created': return 'bg-green-500/10 text-green-400 border-green-500/20';
        case 'updated': return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
        case 'deleted': return 'bg-red-500/10 text-red-400 border-red-500/20';
        default: return 'bg-gray-500/10 text-gray-400 border-gray-500/20';
    }
};

// Get subject type badge color
const getSubjectTypeColor = (type) => {
    switch (type) {
        case 'Application': return 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20';
        case 'Job': return 'bg-purple-500/10 text-purple-400 border-purple-500/20';
        case 'User': return 'bg-orange-500/10 text-orange-400 border-orange-500/20';
        default: return 'bg-gray-500/10 text-gray-400 border-gray-500/20';
    }
};

// Handle pagination
const goToPage = (page) => {
    if (page < 1 || page > lastPage.value) return;
    router.visit(route('admin.audit-logs.index', { 
        page,
        per_page: perPageLogs.value
    }));
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

const changePerPage = () => {
    router.get(route('admin.audit-logs.index'), {
        per_page: perPageLogs.value,
        page: 1
    });
};

// Show details
const viewDetails = (log) => {
    selectedLog.value = log;
    showDetailModal.value = true;
};

// Format change value
const formatChangeValue = (value) => {
    if (typeof value === 'boolean') return value ? 'Yes' : 'No';
    if (value === null) return 'N/A';
    return String(value);
};
</script>

<template>
    <Head title="Dryex Admin - Audit Logs" />

    <AdminPageLayout title="Audit Logs 🔐" subtitle="Track all system activities and data changes for security and compliance">
        <!-- Search and Filter Section -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div class="col-span-1 md:col-span-2">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by admin name, applicant name, or action..."
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.05] transition-all"
                />
            </div>
            
            <!-- Filter by Type -->
            <div class="col-span-1">
                <select
                    v-model="filterType"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.05] transition-all"
                >
                    <option value="all" class="bg-gray-900 text-white">All Types</option>
                    <option value="Application" class="bg-gray-900 text-white">Applications Only</option>
                    <option value="Job" class="bg-gray-900 text-white">Jobs Only</option>
                    <option value="User" class="bg-gray-900 text-white">Users Only</option>
                </select>
            </div>
        </div>

        <!-- Results Count & Per Page -->
        <div class="mb-4 flex items-center justify-between flex-wrap gap-4">
            <div class="text-sm text-gray-400">
                Tampilkan <span class="font-bold text-cyan-400">{{ startRecord }}-{{ endRecord }}</span> dari <span class="font-bold text-cyan-400">{{ totalLogs }}</span> audit log
                <span class="text-gray-500 ml-2">(Halaman {{ currentPage }} dari {{ lastPage }})</span>
            </div>
            
            <!-- Per Page Selector -->
            <div class="flex items-center gap-3">
                <label class="text-sm text-gray-400">Items per halaman:</label>
                <select
                    v-model="perPageLogs"
                    @change="changePerPage"
                    class="px-3 py-2 bg-white/10 border border-white/20 rounded-lg text-white text-sm focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.05] transition-all"
                >
                    <option value="15" class="bg-gray-900">15</option>
                    <option value="25" class="bg-gray-900">25</option>
                    <option value="50" class="bg-gray-900">50</option>
                    <option value="100" class="bg-gray-900">100</option>
                </select>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] overflow-hidden shadow-inner mb-8">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 bg-white/[0.02]">
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Timestamp</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Admin/User</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Activity</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Changes</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="log in filteredLogs"
                            :key="log.id"
                            class="border-b border-white/5 hover:bg-white/[0.02] transition-colors"
                        >
                            <!-- Timestamp -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="text-sm font-bold text-white">{{ log.timestamp }}</p>
                                    <p class="text-xs text-gray-500">{{ log.time_ago }}</p>
                                </div>
                            </td>

                            <!-- Admin/User -->
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-bold text-white">{{ log.causer_name }}</p>
                                    <p class="text-xs text-gray-500">{{ log.causer_email }}</p>
                                </div>
                            </td>

                            <!-- Activity Description -->
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-300 max-w-xs line-clamp-2">{{ log.description }}</p>
                            </td>

                            <!-- Type Badge -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['px-3 py-1 rounded-full text-xs font-bold border', getSubjectTypeColor(log.subject_type)]">
                                    {{ log.subject_type }}
                                </span>
                            </td>

                            <!-- Changes Count -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                    {{ Object.keys(log.changes).length }} field{{ Object.keys(log.changes).length !== 1 ? 's' : '' }}
                                </span>
                            </td>

                            <!-- View Details Button -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button
                                    @click="viewDetails(log)"
                                    class="px-3 py-1 bg-cyan-600 hover:bg-cyan-700 text-cyan-50 rounded-lg text-xs font-bold transition-colors"
                                >
                                    View Details
                                </button>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="filteredLogs.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-gray-400 font-medium">No audit logs found</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div v-if="lastPage > 1" class="flex items-center justify-center gap-1 mb-8 flex-wrap">
            <!-- Previous Button -->
            <button
                @click="prevPage"
                :disabled="currentPage === 1"
                class="px-4 py-2 bg-white/10 hover:bg-white/20 disabled:opacity-50 disabled:cursor-not-allowed border border-white/20 rounded-lg text-white font-semibold transition-colors"
            >
                ← Previous
            </button>

            <!-- First Page (if not visible in range) -->
            <button
                v-if="pageNumbers[0] > 1"
                @click="goToPage(1)"
                :class="[
                    'px-3 py-2 rounded-lg font-semibold transition-colors',
                    currentPage === 1
                        ? 'bg-cyan-600 text-white'
                        : 'bg-white/10 hover:bg-white/20 text-gray-300 border border-white/20'
                ]"
            >
                1
            </button>

            <!-- Ellipsis (left) -->
            <span v-if="pageNumbers[0] > 2" class="px-2 text-gray-400">...</span>

            <!-- Page Numbers -->
            <button
                v-for="page in pageNumbers"
                :key="page"
                @click="goToPage(page)"
                :class="[
                    'px-3 py-2 rounded-lg font-semibold transition-colors',
                    page === currentPage
                        ? 'bg-cyan-600 text-white'
                        : 'bg-white/10 hover:bg-white/20 text-gray-300 border border-white/20'
                ]"
            >
                {{ page }}
            </button>

            <!-- Ellipsis (right) -->
            <span v-if="pageNumbers[pageNumbers.length - 1] < lastPage - 1" class="px-2 text-gray-400">...</span>

            <!-- Last Page (if not visible in range) -->
            <button
                v-if="pageNumbers[pageNumbers.length - 1] < lastPage"
                @click="goToPage(lastPage)"
                :class="[
                    'px-3 py-2 rounded-lg font-semibold transition-colors',
                    currentPage === lastPage
                        ? 'bg-cyan-600 text-white'
                        : 'bg-white/10 hover:bg-white/20 text-gray-300 border border-white/20'
                ]"
            >
                {{ lastPage }}
            </button>

            <!-- Next Button -->
            <button
                @click="nextPage"
                :disabled="currentPage === lastPage"
                class="px-4 py-2 bg-white/10 hover:bg-white/20 disabled:opacity-50 disabled:cursor-not-allowed border border-white/20 rounded-lg text-white font-semibold transition-colors"
            >
                Next →
            </button>
        </div>

        <!-- Detail Modal -->
        <div
            v-if="showDetailModal && selectedLog"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-gray-900 border border-white/10 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="sticky top-0 bg-gray-900 border-b border-white/10 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-black text-white">Audit Log Details</h2>
                    <button
                        @click="showDetailModal = false"
                        class="text-gray-400 hover:text-white transition-colors text-2xl"
                    >
                        ✕
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6">
                    <!-- Basic Info -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-wider">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-bold mb-1">TIMESTAMP</p>
                                <p class="text-white font-semibold">{{ selectedLog.timestamp }}</p>
                            </div>
                            <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-bold mb-1">LOG ID</p>
                                <p class="text-white font-semibold">#{{ selectedLog.id }}</p>
                            </div>
                            <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-bold mb-1">TYPE</p>
                                <span :class="['px-3 py-1 rounded-full text-xs font-bold border inline-block', getSubjectTypeColor(selectedLog.subject_type)]">
                                    {{ selectedLog.subject_type }}
                                </span>
                            </div>
                            <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-bold mb-1">SUBJECT ID</p>
                                <p class="text-white font-semibold">#{{ selectedLog.subject_id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Info -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-wider">Performed By</h3>
                        <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 font-black">
                                    {{ selectedLog.causer_name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-white">{{ selectedLog.causer_name }}</p>
                                    <p class="text-xs text-gray-500">{{ selectedLog.causer_email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Description -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-wider">Activity Description</h3>
                        <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                            <p class="text-white text-sm leading-relaxed">{{ selectedLog.description }}</p>
                        </div>
                    </div>

                    <!-- Changes/Attributes -->
                    <div v-if="Object.keys(selectedLog.changes).length > 0" class="space-y-4">
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-wider">
                            Data Changes ({{ Object.keys(selectedLog.changes).length }})
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="(value, key) in selectedLog.changes"
                                :key="key"
                                class="bg-white/5 border border-white/10 rounded-lg p-4"
                            >
                                <p class="text-xs text-gray-500 font-bold mb-2 uppercase">{{ key }}</p>
                                <p class="text-white font-semibold break-words">{{ formatChangeValue(value) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- No Changes -->
                    <div v-else class="bg-white/5 border border-white/10 rounded-lg p-4 text-center">
                        <p class="text-gray-500 text-sm">No data changes recorded for this activity</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminPageLayout>
</template>
