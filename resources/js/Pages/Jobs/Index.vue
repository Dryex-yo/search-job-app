<script setup>
import { ref, watch, computed } from 'vue';
import { router, Head, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import axios from 'axios';
import CVPreviewModal from '@/Components/CVPreviewModal.vue';
import NotificationContainer from '@/Components/NotificationContainer.vue';
import { useNotification } from '@/Composables/useNotification';

// 1. Definisikan Props
const props = defineProps({ 
    jobs: Array,
    filters: Object,
    jobTypes: Array,
    locations: Array,
});

// 0.5. Initialize notification
const { success: showSuccess, error: showError, warning: showWarning, info: showInfo } = useNotification();

// 2. Deklarasikan State (Urutan sangat penting!)
const search = ref(props.filters?.search || '');
const selectedType = ref(props.filters?.type || '');
const selectedLocation = ref(props.filters?.location || '');
const salaryMin = ref(props.filters?.salary_min || '');
const salaryMax = ref(props.filters?.salary_max || '');
const showFilters = ref(false);
const selectedJob = ref(null);
const isModalOpen = ref(false);
const isLoading = ref(false);
const showCVPreview = ref(false);
const previewResumePath = ref(null);

// 3. Computed Property: Check if any filter is active
const hasActiveFilters = computed(() => {
    return search.value || selectedType.value || selectedLocation.value || salaryMin.value || salaryMax.value;
});

// 4. Apply Filters function (define before using in watch)
const applyFilters = () => {
    const filterParams = {};
    
    if (search.value) filterParams.search = search.value;
    if (selectedType.value) filterParams.type = selectedType.value;
    if (selectedLocation.value) filterParams.location = selectedLocation.value;
    if (salaryMin.value) filterParams.salary_min = salaryMin.value;
    if (salaryMax.value) filterParams.salary_max = salaryMax.value;
    
    router.get('/', filterParams, {
        preserveState: true,
        replace: true
    });
};

// 5. Logika Pencarian & Filter (Reaktif)
const debouncedSearch = debounce(() => {
    applyFilters();
}, 300);

watch(search, debouncedSearch);
watch([selectedType, selectedLocation, salaryMin, salaryMax], applyFilters);

// 6. Reset Filters
const resetFilters = () => {
    search.value = '';
    selectedType.value = '';
    selectedLocation.value = '';
    salaryMin.value = '';
    salaryMax.value = '';
    router.get('/', {}, {
        preserveState: true,
        replace: true
    });
};

// 7. Fungsi Modal & Detail
const openDetail = async (id) => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/jobs/${id}`);
        selectedJob.value = response.data;
        isModalOpen.value = true;
    } catch (err) {
        console.error("Gagal mengambil detail job", err);
        showError('Gagal Memuat', 'Tidak dapat mengambil detail lowongan. Silakan coba lagi.');
    } finally {
        isLoading.value = false;
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedJob.value = null;
};

const applyForm = useForm({
    job_id: null,
    resume: null,
    cover_letter: '',
});

const submitApply = () => {
    applyForm.job_id = selectedJob.value.id;
    applyForm.post(route('jobs.apply'), {
        onSuccess: () => {
            closeModal();
            applyForm.reset();
            showSuccess('Lamaran Terkirim!', 'Lamaran Anda telah berhasil dikirim ke Dryex. Semoga berhasil!');
        },
        onError: (errors) => {
            console.error('Apply error:', errors);
            showError('Gagal Mengirim Lamaran', Object.values(errors).flat().join(', '));
        }
    });
};

const openCVPreview = () => {
    if (applyForm.resume) {
        previewResumePath.value = URL.createObjectURL(applyForm.resume);
        showCVPreview.value = true;
    }
};

const closeCVPreview = () => {
    showCVPreview.value = false;
    if (previewResumePath.value && previewResumePath.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewResumePath.value);
    }
    previewResumePath.value = null;
};
</script>

<template>
    <Head title="Dryex - Temukan Karir Impianmu" />
    
    <!-- Notification Container -->
    <NotificationContainer />
    
    <div class="min-h-screen bg-[#0b0f1a] text-white p-6 md:p-12 font-sans relative overflow-hidden">
        
        <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-cyan-500/10 blur-[150px] rounded-full"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[600px] h-[600px] bg-blue-600/10 blur-[150px] rounded-full"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            
            <header class="flex flex-col md:flex-row justify-between items-center mb-8 gap-6">
                <div>
                    <h1 class="text-4xl font-black text-cyan-400 tracking-tighter italic uppercase">
                        Dryex<span class="text-white not-italic">.</span>
                    </h1>
                    <p class="text-gray-400 text-sm mt-1">Platform Pencarian Kerja Masa Depan</p>
                </div>
                
                <div class="relative w-full max-w-xl group">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Cari posisi, perusahaan, atau lokasi..."
                        class="w-full bg-white/5 backdrop-blur-2xl border border-white/10 rounded-[2rem] py-4 px-8 text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500/50 focus:ring-4 focus:ring-cyan-500/10 transition-all shadow-2xl"
                    />
                    <div class="absolute right-6 top-1/2 -translate-y-1/2 text-cyan-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </header>

            <!-- Filter Section -->
            <div class="mb-8 bg-white/5 backdrop-blur-xl border border-white/10 rounded-[2rem] p-6">
                <div class="flex justify-between items-center mb-4">
                    <button 
                        @click="showFilters = !showFilters"
                        class="flex items-center gap-2 text-cyan-400 hover:text-cyan-300 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span class="font-bold">Filter Pencarian</span>
                        <span v-if="hasActiveFilters" class="bg-cyan-500 text-slate-900 px-2 py-1 rounded-full text-xs font-bold">{{ Object.values(filters).filter(Boolean).length }}</span>
                    </button>
                    
                    <button 
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="text-gray-400 hover:text-white text-sm font-medium transition-colors"
                    >
                        Reset Filter
                    </button>
                </div>

                <!-- Filter Options -->
                <div v-if="showFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                    
                    <!-- Filter: Job Type -->
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Tipe Pekerjaan</label>
                        <select 
                            v-model="selectedType"
                            class="w-full bg-white/10 border border-white/10 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/10 transition-all"
                        >
                            <option value="">Semua Tipe</option>
                            <option v-for="type in jobTypes" :key="type" :value="type">
                                {{ type }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter: Location -->
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Lokasi</label>
                        <select 
                            v-model="selectedLocation"
                            class="w-full bg-white/10 border border-white/10 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/10 transition-all"
                        >
                            <option value="">Semua Lokasi</option>
                            <option v-for="location in locations" :key="location" :value="location">
                                {{ location }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter: Salary Min -->
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Gaji Min</label>
                        <input 
                            v-model="salaryMin"
                            type="text"
                            placeholder="Cth: Rp 5.000.000"
                            class="w-full bg-white/10 border border-white/10 rounded-xl px-3 py-2 text-white text-sm placeholder-gray-600 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/10 transition-all"
                        />
                    </div>

                    <!-- Filter: Salary Max -->
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Gaji Max</label>
                        <input 
                            v-model="salaryMax"
                            type="text"
                            placeholder="Cth: Rp 15.000.000"
                            class="w-full bg-white/10 border border-white/10 rounded-xl px-3 py-2 text-white text-sm placeholder-gray-600 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/10 transition-all"
                        />
                    </div>

                </div>
            </div>

            <!-- Active Filters Display -->
            <div v-if="hasActiveFilters" class="mb-6 flex flex-wrap gap-2">
                <span v-if="search" class="bg-cyan-500/20 border border-cyan-500/30 text-cyan-300 px-3 py-1 rounded-full text-xs font-medium">
                    🔍 {{ search }}
                </span>
                <span v-if="selectedType" class="bg-cyan-500/20 border border-cyan-500/30 text-cyan-300 px-3 py-1 rounded-full text-xs font-medium">
                    💼 {{ selectedType }}
                </span>
                <span v-if="selectedLocation" class="bg-cyan-500/20 border border-cyan-500/30 text-cyan-300 px-3 py-1 rounded-full text-xs font-medium">
                    📍 {{ selectedLocation }}
                </span>
                <span v-if="salaryMin || salaryMax" class="bg-cyan-500/20 border border-cyan-500/30 text-cyan-300 px-3 py-1 rounded-full text-xs font-medium">
                    💰 {{ salaryMin || 'Min' }} - {{ salaryMax || 'Max' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <div v-if="jobs.length === 0" class="col-span-full text-center py-24 bg-white/5 rounded-[3rem] border border-dashed border-white/10">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-400 text-xl italic">Wah, lowongan tidak tersedia dengan filter yang Anda pilih.</p>
                    <button 
                        @click="resetFilters"
                        class="mt-4 text-cyan-400 hover:text-cyan-300 text-sm font-medium transition-colors"
                    >
                        Coba reset filter
                    </button>
                </div>

                <div 
                    v-for="job in jobs" 
                    :key="job.id" 
                    class="group relative bg-white/5 backdrop-blur-xl border border-white/10 p-8 rounded-[2.5rem] shadow-xl hover:border-cyan-500/50 transition-all duration-500 flex flex-col"
                >
                    <div class="absolute top-6 right-8 w-1.5 h-1.5 bg-cyan-400/40 rounded-full blur-[1px]"></div>
                    
                    <div class="mb-6">
                        <span class="bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                            {{ job.type }}
                        </span>
                    </div>

                    <div class="flex-grow">
                        <h2 class="text-2xl font-bold mb-2 group-hover:text-cyan-400 transition-colors duration-300 leading-tight">
                            {{ job.title }}
                        </h2>
                        <p class="text-gray-400 text-sm font-medium mb-1">{{ job.company_name }}</p>
                        <p class="text-gray-500 text-xs mb-6 flex items-center">
                            <svg class="w-3 h-3 mr-1 text-cyan-500/60" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            {{ job.location }}
                        </p>
                    </div>

                    <div class="mt-4 pt-6 border-t border-white/5 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase tracking-tighter">Gaji Estimasian</p>
                            <p class="text-lg font-black text-white leading-none mt-1">{{ job.salary }}</p>
                        </div>
                        <button 
                            @click="openDetail(job.id)" 
                            class="bg-white/10 hover:bg-cyan-500 hover:text-slate-900 border border-white/10 px-6 py-3 rounded-2xl text-xs font-black transition-all duration-300"
                        >
                            DETAIL
                        </button>
                    </div>
                </div>

            </div>

            <footer class="mt-20 text-center text-gray-600 text-[10px] uppercase tracking-[0.3em]">
                &copy; 2026 Dryex Ecosystem • Powered by Laravel Inertia
            </footer>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div @click="closeModal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

            <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 w-full max-w-2xl p-8 rounded-[3rem] shadow-2xl text-white overflow-hidden">
            <div class="absolute top-0 right-0 p-6">
                <button @click="closeModal" class="text-gray-400 hover:text-white text-xl">✕</button>
            </div>

            <div v-if="selectedJob">
                <span class="text-cyan-400 text-xs font-black tracking-widest uppercase">{{ selectedJob.type }}</span>
                <h2 class="text-3xl font-bold mt-2 mb-1">{{ selectedJob.title }}</h2>
                <p class="text-gray-400 mb-6">{{ selectedJob.company_name }} • {{ selectedJob.location }}</p>
                
                <div class="h-px bg-white/10 mb-6"></div>
                
                <div class="prose prose-invert max-h-48 overflow-y-auto mb-8 pr-4 custom-scrollbar text-gray-300">
                    <h4 class="text-white font-bold mb-2 text-sm uppercase tracking-wider">Deskripsi Pekerjaan</h4>
                    <p class="leading-relaxed text-sm">{{ selectedJob.description }}</p>
                </div>

                <div class="border-t border-white/10 pt-6">
                    <div class="flex flex-col gap-4">
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase tracking-tighter">Gaji Estimasian</p>
                                <p class="text-2xl font-black text-cyan-400">{{ selectedJob.salary }}</p>
                            </div>
                        </div>

                        <div class="space-y-4 w-full">
                            <div class="bg-white/5 p-4 rounded-2xl border border-white/10">
                                <label class="block text-[10px] text-gray-400 uppercase mb-2 ml-1">Upload CV (PDF, Max 2MB)</label>
                                <div class="flex gap-2 items-end">
                                    <div class="flex-grow">
                                        <input type="file" 
                                            @input="applyForm.resume = $event.target.files[0]" 
                                            accept=".pdf"
                                            class="text-xs text-gray-300 w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:bg-cyan-500/20 file:text-cyan-400 hover:file:bg-cyan-500/30 cursor-pointer"
                                        />
                                    </div>
                                    <button 
                                        v-if="applyForm.resume"
                                        @click="openCVPreview"
                                        class="px-4 py-2 bg-white/10 border border-white/10 rounded-xl text-[10px] font-bold text-gray-300 hover:text-cyan-400 hover:border-cyan-500/50 hover:bg-cyan-500/10 transition-all uppercase tracking-wider whitespace-nowrap"
                                        title="Preview uploaded CV"
                                    >
                                        👁 Preview
                                    </button>
                                </div>
                            </div>

                            <button @click="submitApply" :disabled="applyForm.processing || !applyForm.resume"
                                class="w-full bg-cyan-500 disabled:bg-gray-600 disabled:cursor-not-allowed text-slate-900 py-4 rounded-2xl font-black shadow-lg shadow-cyan-500/20 active:scale-95 transition-all uppercase tracking-widest text-sm">
                                {{ applyForm.processing ? 'MENGIRIM...' : 'KIRIM LAMARAN SEKARANG' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- CV Preview Modal -->
    <CVPreviewModal 
        :show="showCVPreview"
        :cv-path="previewResumePath"
        candidate-name="Your CV"
        title="Preview CV"
        @close="closeCVPreview"
    />
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(6, 182, 212, 0.3);
    border-radius: 10px;
}
</style>