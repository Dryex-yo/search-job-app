<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminPageLayout from '@/Layouts/AdminPageLayout.vue';

const props = defineProps({
    analytics: {
        type: Object,
        required: true
    },
    jobs: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            type: '',
        })
    }
});

// Modal states
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedJob = ref(null);

// Form state
const form = useForm({
    title: '',
    company_name: '',
    location: '',
    salary: '',
    description: '',
    type: 'Full-time',
    status: 'active',
});

// Filter state
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const typeFilter = ref(props.filters.type || '');

// Computed properties
const filteredJobs = computed(() => {
    let filtered = props.jobs;

    if (searchQuery.value) {
        filtered = filtered.filter(job =>
            job.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            job.location.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    if (statusFilter.value) {
        filtered = filtered.filter(job => job.status === statusFilter.value);
    }

    if (typeFilter.value) {
        filtered = filtered.filter(job => job.type === typeFilter.value);
    }

    return filtered;
});

// Methods
const openCreateModal = () => {
    form.reset();
    showCreateModal.value = true;
};

const openEditModal = (job) => {
    selectedJob.value = job;
    form.title = job.title;
    form.company_name = job.company_name;
    form.location = job.location;
    form.salary = job.salary;
    form.description = job.description;
    form.type = job.type;
    form.status = job.status;
    showEditModal.value = true;
};

const openDeleteModal = (job) => {
    selectedJob.value = job;
    showDeleteModal.value = true;
};

const submitForm = async () => {
    console.log('Form submitted:', { 
        isEdit: showEditModal.value, 
        jobId: selectedJob.value?.id,
        formData: form.data()
    });
    
    try {
        if (showEditModal.value && selectedJob.value) {
            await form.patch(route('admin.jobs.update', selectedJob.value.id), {
                onSuccess: () => {
                    console.log('Job updated successfully');
                    showEditModal.value = false;
                    form.reset();
                    selectedJob.value = null;
                    setTimeout(() => window.location.reload(), 500);
                },
                onError: (errors) => {
                    console.error('Update error:', errors);
                    alert('Error updating job: ' + JSON.stringify(errors));
                }
            });
        } else {
            await form.post(route('admin.jobs.store'), {
                onSuccess: () => {
                    console.log('Job created successfully');
                    showCreateModal.value = false;
                    form.reset();
                    setTimeout(() => window.location.reload(), 500);
                },
                onError: (errors) => {
                    console.error('Create error:', errors);
                    alert('Error creating job: ' + JSON.stringify(errors));
                }
            });
        }
    } catch (error) {
        console.error('Form submission error:', error);
        alert('Error: ' + error.message);
    }
};

const confirmDelete = () => {
    if (selectedJob.value) {
        console.log('Deleting job:', selectedJob.value.id);
        router.delete(route('admin.jobs.destroy', selectedJob.value.id), {
            onSuccess: () => {
                console.log('Job deleted successfully');
                showDeleteModal.value = false;
                selectedJob.value = null;
                setTimeout(() => window.location.reload(), 500);
            },
            onError: (errors) => {
                console.error('Delete error:', errors);
                alert('Error deleting job: ' + JSON.stringify(errors));
                showDeleteModal.value = false;
            }
        });
    }
};

const toggleStatus = (job) => {
    const newStatus = job.status === 'active' ? 'inactive' : 'active';
    console.log('Toggling job status:', { jobId: job.id, currentStatus: job.status, newStatus: newStatus });
    
    router.patch(route('admin.jobs.update', job.id), { 
        ...job,
        status: newStatus 
    }, {
        onSuccess: () => {
            console.log('Status toggled successfully');
            setTimeout(() => window.location.reload(), 300);
        },
        onError: (errors) => {
            console.error('Status toggle error:', errors);
            alert('Error toggling status: ' + JSON.stringify(errors));
        }
    });
};

const applyFilters = () => {
    router.get(route('admin.jobs'), {
        search: searchQuery.value,
        status: statusFilter.value,
        type: typeFilter.value,
    });
};

const resetFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    typeFilter.value = '';
    router.get(route('admin.jobs'));
};
</script>

<template>
    <Head title="Dryex Admin - Jobs Management" />

    <AdminPageLayout title="Jobs Management 💼" subtitle="Manage and monitor all job listings">
        <!-- Quick Actions -->
        <div class="mb-12 grid grid-cols-1 md:grid-cols-3 gap-6">
            <button @click="openCreateModal" class="bg-gradient-to-br from-cyan-500/20 to-cyan-500/5 border border-cyan-500/30 hover:border-cyan-500/60 rounded-2xl p-6 text-left transition-all hover:shadow-lg hover:shadow-cyan-500/20">
                <p class="text-3xl mb-2">➕</p>
                <p class="font-bold text-white mb-1">Create New Job</p>
                <p class="text-xs text-gray-600 dark:text-gray-400">Add a new job listing to the platform</p>
            </button>

            <button @click="statusFilter = 'active'; applyFilters()" class="bg-gradient-to-br from-blue-500/20 to-blue-500/5 border border-blue-500/30 hover:border-blue-500/60 rounded-2xl p-6 text-left transition-all hover:shadow-lg hover:shadow-blue-500/20">
                <p class="text-3xl mb-2">📋</p>
                <p class="font-bold text-white mb-1">Active Listings</p>
                <p class="text-lg font-black text-blue-400">{{ analytics.active_jobs }}</p>
            </button>

            <button class="bg-gradient-to-br from-green-500/20 to-green-500/5 border border-green-500/30 hover:border-green-500/60 rounded-2xl p-6 text-left transition-all hover:shadow-lg hover:shadow-green-500/20">
                <p class="text-3xl mb-2">🎯</p>
                <p class="font-bold text-white mb-1">Total Applications</p>
                <p class="text-lg font-black text-green-400">{{ analytics.total_applications }}</p>
            </button>
        </div>

        <!-- Search & Filter Section -->
        <div class="mb-8 bg-white/[0.01] border border-white/10 rounded-2xl p-6">
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <div class="flex-1">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="🔍 Search by job title or location..." 
                        class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-500 focus:border-cyan-500/60 focus:outline-none transition"
                        @keyup.enter="applyFilters"
                    >
                </div>
                <select 
                    v-model="statusFilter"
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:border-cyan-500/60 focus:outline-none transition"
                    @change="applyFilters"
                >
                    <option value="">Status: All</option>
                    <option value="active">Active 🟢</option>
                    <option value="inactive">Inactive ⚫</option>
                </select>
                <select 
                    v-model="typeFilter"
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:border-cyan-500/60 focus:outline-none transition"
                    @change="applyFilters"
                >
                    <option value="">Type: All</option>
                    <option value="Full-time">Full-time 💼</option>
                    <option value="Part-time">Part-time ⏱️</option>
                    <option value="Contract">Contract 📋</option>
                    <option value="Freelance">Freelance 🚀</option>
                </select>
                <button 
                    @click="applyFilters"
                    class="px-6 py-2 bg-cyan-500/20 border border-cyan-500/60 hover:bg-cyan-500/30 rounded-lg text-white font-semibold transition"
                >
                    Search
                </button>
                <button 
                    @click="resetFilters"
                    class="px-6 py-2 bg-gray-500/20 border border-gray-500/60 hover:bg-gray-500/30 rounded-lg text-white font-semibold transition"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Job Stats Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-12">
            <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                <h4 class="text-[10px] font-black text-gray-700 dark:text-gray-400 uppercase tracking-[0.5em] mb-8 italic">Job Listings Overview</h4>
                
                <div class="space-y-8">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-400">Total Jobs Created</p>
                            <p class="text-2xl font-black text-white">{{ analytics.total_jobs }}</p>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-cyan-600 to-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.7)]" style="width: 100%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-400">Currently Active</p>
                            <p class="text-2xl font-black text-cyan-400">{{ analytics.active_jobs }}</p>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-cyan-600 to-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.7)]" :style="{width: Math.round((analytics.active_jobs / analytics.total_jobs) * 100) + '%' || '0%'}"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-400">Closed/Archived</p>
                            <p class="text-2xl font-black text-gray-600 dark:text-gray-400">{{ analytics.total_jobs - analytics.active_jobs }}</p>
                        </div>
                        <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-gray-600 to-gray-500 shadow-[0_0_20px_rgba(107,114,128,0.7)]" :style="{width: Math.round(((analytics.total_jobs - analytics.active_jobs) / analytics.total_jobs) * 100) + '%' || '0%'}"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                <h4 class="text-[10px] font-black text-gray-700 dark:text-gray-400 uppercase tracking-[0.5em] mb-8 italic">Application Distribution</h4>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⏳</span>
                            <span class="font-bold text-gray-600 dark:text-gray-400">Pending</span>
                        </div>
                        <span class="text-xl font-black text-orange-400">{{ analytics.pending_applications }}</span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⭐</span>
                            <span class="font-bold text-gray-600 dark:text-gray-400">Shortlisted</span>
                        </div>
                        <span class="text-xl font-black text-blue-400">{{ analytics.shortlisted_applications }}</span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">✅</span>
                            <span class="font-bold text-gray-600 dark:text-gray-400">Hired</span>
                        </div>
                        <span class="text-xl font-black text-green-400">{{ analytics.hired_count }}</span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">❌</span>
                            <span class="font-bold text-gray-600 dark:text-gray-400">Declined</span>
                        </div>
                        <span class="text-xl font-black text-red-400">{{ analytics.rejected_applications }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jobs Table Section -->
        <div class="bg-white/[0.01] border border-white/10 rounded-[2.5rem] p-8">
            <h4 class="text-[10px] font-black text-gray-700 dark:text-gray-400 uppercase tracking-[0.5em] mb-8 italic">📊 All Jobs ({{ filteredJobs.length }})</h4>
            
            <div v-if="filteredJobs.length === 0" class="text-center py-12">
                <p class="text-gray-600 dark:text-gray-400 text-lg">No jobs found. Create one to get started! 🚀</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-400 uppercase">Title</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-400 uppercase">Location</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-400 uppercase">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-400 uppercase">Salary</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 dark:text-gray-400 uppercase">Applications</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="job in filteredJobs" :key="job.id" class="border-b border-white/5 hover:bg-white/[0.02] transition">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-bold text-white">{{ job.title }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ job.company_name }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">📍 {{ job.location }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-blue-500/20 border border-blue-500/30 rounded-full text-xs font-semibold text-blue-300">
                                    {{ job.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ job.salary }}</td>
                            <td class="px-6 py-4 text-center">
                                <button 
                                    @click="toggleStatus(job)"
                                    :class="{
                                        'px-3 py-1 rounded-full text-xs font-semibold transition': true,
                                        'bg-green-500/20 border border-green-500/30 text-green-300': job.status === 'active',
                                        'bg-gray-500/20 border border-gray-500/30 text-gray-300': job.status === 'inactive'
                                    }"
                                >
                                    {{ job.status === 'active' ? '🟢 Active' : '⚫ Inactive' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2 text-xs">
                                    <span class="text-gray-600 dark:text-gray-400">📝 {{ job.applications_count }}</span>
                                    <span v-if="job.hired_count > 0" class="text-green-400">✅ {{ job.hired_count }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <button 
                                        @click="openEditModal(job)"
                                        class="p-2 bg-blue-500/20 hover:bg-blue-500/30 border border-blue-500/30 rounded-lg text-blue-300 transition"
                                        title="Edit"
                                    >
                                        ✏️
                                    </button>
                                    <button 
                                        @click="openDeleteModal(job)"
                                        class="p-2 bg-red-500/20 hover:bg-red-500/30 border border-red-500/30 rounded-lg text-red-300 transition"
                                        title="Delete"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminPageLayout>

    <!-- Create/Edit Job Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-gray-900 border border-white/10 rounded-2xl p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-2xl font-bold text-white mb-6">{{ showEditModal ? '✏️ Edit Job' : '➕ Create New Job' }}</h3>

            <form @submit.prevent="submitForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-400 mb-2">Job Title *</label>
                        <input 
                            v-model="form.title"
                            type="text" 
                            placeholder="e.g. Senior Frontend Developer"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 focus:border-cyan-500/60 focus:outline-none transition"
                        >
                        <p v-if="form.errors.title" class="text-red-400 text-xs mt-1">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-400 mb-2">Company Name *</label>
                        <input 
                            v-model="form.company_name"
                            type="text" 
                            placeholder="e.g. Tech Corp"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 focus:border-cyan-500/60 focus:outline-none transition"
                        >
                        <p v-if="form.errors.company_name" class="text-red-400 text-xs mt-1">{{ form.errors.company_name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-400 mb-2">Location *</label>
                        <input 
                            v-model="form.location"
                            type="text" 
                            placeholder="e.g. Jakarta, Indonesia"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 focus:border-cyan-500/60 focus:outline-none transition"
                        >
                        <p v-if="form.errors.location" class="text-red-400 text-xs mt-1">{{ form.errors.location }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-400 mb-2">Salary *</label>
                        <input 
                            v-model="form.salary"
                            type="text" 
                            placeholder="e.g. Rp 10-15 Juta"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 focus:border-cyan-500/60 focus:outline-none transition"
                        >
                        <p v-if="form.errors.salary" class="text-red-400 text-xs mt-1">{{ form.errors.salary }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-400 mb-2">Job Type *</label>
                        <select 
                            v-model="form.type"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:border-cyan-500/60 focus:outline-none transition"
                        >
                            <option value="Full-time">Full-time 💼</option>
                            <option value="Part-time">Part-time ⏱️</option>
                            <option value="Contract">Contract 📋</option>
                            <option value="Freelance">Freelance 🚀</option>
                        </select>
                        <p v-if="form.errors.type" class="text-red-400 text-xs mt-1">{{ form.errors.type }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-400 mb-2">Status *</label>
                        <select 
                            v-model="form.status"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:border-cyan-500/60 focus:outline-none transition"
                        >
                            <option value="active">Active 🟢</option>
                            <option value="inactive">Inactive ⚫</option>
                        </select>
                        <p v-if="form.errors.status" class="text-red-400 text-xs mt-1">{{ form.errors.status }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2">Job Description *</label>
                    <textarea 
                        v-model="form.description"
                        placeholder="Enter detailed job description..."
                        rows="5"
                        class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-600 focus:border-cyan-500/60 focus:outline-none transition"
                    ></textarea>
                    <p v-if="form.errors.description" class="text-red-400 text-xs mt-1">{{ form.errors.description }}</p>
                </div>

                <div class="flex gap-4 justify-end">
                    <button 
                        type="button"
                        @click="() => { showCreateModal = false; showEditModal = false; }"
                        class="px-6 py-2 bg-gray-500/20 border border-gray-500/60 hover:bg-gray-500/30 rounded-lg text-white font-semibold transition"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-cyan-500/20 border border-cyan-500/60 hover:bg-cyan-500/30 rounded-lg text-white font-semibold transition disabled:opacity-50"
                    >
                        {{ form.processing ? '⏳ Loading...' : (showEditModal ? '✅ Update Job' : '✅ Create Job') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal && selectedJob" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-gray-900 border border-red-500/30 rounded-2xl p-8 max-w-md w-full mx-4">
            <h3 class="text-2xl font-bold text-white mb-4">🗑️ Delete Job?</h3>
            <p class="text-gray-300 mb-6">
                Are you sure you want to delete <strong>{{ selectedJob.title }}</strong>? This action cannot be undone and all related applications will also be deleted.
            </p>

            <div class="flex gap-4 justify-end">
                <button 
                    @click="showDeleteModal = false"
                    class="px-6 py-2 bg-gray-500/20 border border-gray-500/60 hover:bg-gray-500/30 rounded-lg text-white font-semibold transition"
                >
                    Cancel
                </button>
                <button 
                    @click="confirmDelete"
                    class="px-6 py-2 bg-red-500/20 border border-red-500/60 hover:bg-red-500/30 rounded-lg text-red-300 font-semibold transition"
                >
                    🗑️ Delete Permanently
                </button>
            </div>
        </div>
    </div>
</template>
