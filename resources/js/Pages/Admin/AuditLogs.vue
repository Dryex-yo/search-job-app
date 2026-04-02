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

// Computed
const filteredLogs = computed(() => {
    return props.logs.filter(log => {
        const matchesSearch = !searchQuery.value || 
            log.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.causer_name.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesFilter = filterType.value === 'all' || log.subject_type === filterType.value;
        
        return matchesSearch && matchesFilter;
    });
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
    router.visit(route('admin.audit-logs.index', { page }));
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

        <!-- Results Count -->
        <div class="mb-4 text-sm text-gray-400">
            Showing <span class="font-bold text-cyan-400">{{ filteredLogs.length }}</span> of <span class="font-bold text-cyan-400">{{ pagination.total }}</span> audit logs
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

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-2 mb-8">
            <!-- Previous -->
            <button
                v-if="pagination.current_page > 1"
                @click="goToPage(pagination.current_page - 1)"
                class="px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-white font-semibold transition-colors"
            >
                ← Previous
            </button>

            <!-- Page Numbers -->
            <div class="flex gap-1">
                <button
                    v-for="page in pagination.last_page"
                    :key="page"
                    @click="goToPage(page)"
                    :class="[
                        'px-3 py-2 rounded-lg font-semibold transition-colors',
                        page === pagination.current_page
                            ? 'bg-cyan-600 text-white'
                            : 'bg-white/10 hover:bg-white/20 text-gray-300 border border-white/20'
                    ]"
                >
                    {{ page }}
                </button>
            </div>

            <!-- Next -->
            <button
                v-if="pagination.current_page < pagination.last_page"
                @click="goToPage(pagination.current_page + 1)"
                class="px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-white font-semibold transition-colors"
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
