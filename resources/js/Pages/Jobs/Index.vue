<script setup>
import { ref, watch, computed } from 'vue';
import { router, Head, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import axios from 'axios';
import CVPreviewModal from '../../Components/CVPreviewModal.vue';
import UserLayout from '../../Layouts/UserLayout.vue';
import { useNotification } from '../../Composables/useNotification';

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
const openDetail = (id) => {
    router.visit(route('jobs.show', id));
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

    <UserLayout>
        <div class="bg-[#0b0f1a] text-white font-sans relative overflow-hidden">
            <!-- Responsive background gradients -->
            <div class="absolute top-[-10%] left-[-10%] w-[250px] sm:w-[400px] md:w-[500px] h-[250px] sm:h-[400px] md:h-[500px] bg-cyan-500/10 blur-[80px] sm:blur-[130px] md:blur-[150px] rounded-full pointer-events-none"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[300px] sm:w-[500px] md:w-[600px] h-[300px] sm:h-[500px] md:h-[600px] bg-blue-600/10 blur-[80px] sm:blur-[130px] md:blur-[150px] rounded-full pointer-events-none"></div>

            <div class="max-w-7xl mx-auto relative z-10 p-4 sm:p-6 md:p-12">
            
            <!-- Header -->
            <header class="flex flex-col gap-4 sm:gap-6 mb-8 md:mb-12">
                <div>
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-cyan-400 tracking-tighter italic uppercase leading-tight">
                        Dryex<span class="text-white not-italic">.</span>
                    </h1>
                    <p class="text-gray-400 text-xs sm:text-sm mt-2">Platform Pencarian Kerja Masa Depan</p>
                </div>
                
                <!-- Search Bar -->
                <div class="relative w-full group">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Cari posisi, perusahaan, atau lokasi..."
                        class="w-full bg-white/5 backdrop-blur-2xl border border-white/10 rounded-full md:rounded-[2rem] py-3 sm:py-4 px-4 sm:px-6 md:px-8 text-xs sm:text-sm md:text-base text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500/50 focus:ring-4 focus:ring-cyan-500/10 transition-all shadow-2xl"
                    />
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-cyan-400">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </header>

            <!-- Filter Section -->
            <div class="mb-6 sm:mb-8 bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl md:rounded-[2rem] p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 mb-4">
                    <button 
                        @click="showFilters = !showFilters"
                        class="flex items-center gap-2 text-cyan-400 hover:text-cyan-300 transition-colors text-sm sm:text-base"
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
                        class="text-gray-400 hover:text-white text-xs sm:text-sm font-medium transition-colors whitespace-nowrap"
                    >
                        Reset Filter
                    </button>
                </div>

                <!-- Filter Options -->
                <div v-if="showFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-6">
                    
                    <!-- Filter: Job Type -->
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Tipe Pekerjaan</label>
                        <select 
                            v-model="selectedType"
                            class="w-full bg-white/10 border border-white/10 rounded-lg sm:rounded-xl px-3 py-2 text-xs sm:text-sm text-white focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/10 transition-all"
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
                            class="w-full bg-white/10 border border-white/10 rounded-lg sm:rounded-xl px-3 py-2 text-xs sm:text-sm text-white focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/10 transition-all"
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
                            class="w-full bg-white/10 border border-white/10 rounded-lg sm:rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/10 transition-all"
                        />
                    </div>

                    <!-- Filter: Salary Max -->
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Gaji Max</label>
                        <input 
                            v-model="salaryMax"
                            type="text"
                            placeholder="Cth: Rp 15.000.000"
                            class="w-full bg-white/10 border border-white/10 rounded-lg sm:rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-gray-600 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/10 transition-all"
                        />
                    </div>

                </div>
            </div>

            <!-- Active Filters Display -->
            <div v-if="hasActiveFilters" class="mb-6 flex flex-wrap gap-2">
                <span v-if="search" class="bg-cyan-500/20 border border-cyan-500/30 text-cyan-300 px-2 sm:px-3 py-1 rounded-full text-xs font-medium">
                    🔍 {{ search }}
                </span>
                <span v-if="selectedType" class="bg-cyan-500/20 border border-cyan-500/30 text-cyan-300 px-2 sm:px-3 py-1 rounded-full text-xs font-medium">
                    💼 {{ selectedType }}
                </span>
                <span v-if="selectedLocation" class="bg-cyan-500/20 border border-cyan-500/30 text-cyan-300 px-2 sm:px-3 py-1 rounded-full text-xs font-medium">
                    📍 {{ selectedLocation }}
                </span>
                <span v-if="salaryMin || salaryMax" class="bg-cyan-500/20 border border-cyan-500/30 text-cyan-300 px-2 sm:px-3 py-1 rounded-full text-xs font-medium">
                    💰 {{ salaryMin || 'Min' }} - {{ salaryMax || 'Max' }}
                </span>
            </div>

            <!-- Job Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
                
                <!-- Empty State -->
                <div v-if="jobs.length === 0" class="col-span-full text-center py-16 sm:py-24 bg-white/5 rounded-2xl md:rounded-[3rem] border border-dashed border-white/10">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 sm:mb-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-400 text-base sm:text-xl italic">Wah, lowongan tidak tersedia dengan filter yang Anda pilih.</p>
                    <button 
                        @click="resetFilters"
                        class="mt-4 text-cyan-400 hover:text-cyan-300 text-xs sm:text-sm font-medium transition-colors"
                    >
                        Coba reset filter
                    </button>
                </div>

                <!-- Job Card -->
                <div 
                    v-for="job in jobs" 
                    :key="job.id" 
                    class="group relative bg-white/5 backdrop-blur-xl border border-white/10 p-4 sm:p-6 md:p-8 rounded-xl md:rounded-[2.5rem] shadow-xl hover:border-cyan-500/50 transition-all duration-500 flex flex-col"
                >
                    <div class="absolute top-3 sm:top-6 right-4 sm:right-8 w-1.5 h-1.5 bg-cyan-400/40 rounded-full blur-[1px]"></div>
                    
                    <!-- Job Type Badge -->
                    <div class="mb-4 sm:mb-6">
                        <span class="bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 px-2 sm:px-3 py-1 rounded-full text-[8px] sm:text-[10px] font-black uppercase tracking-widest">
                            {{ job.type }}
                        </span>
                    </div>

                    <!-- Job Info -->
                    <div class="flex-grow">
                        <h2 class="text-lg sm:text-xl md:text-2xl font-bold mb-1 sm:mb-2 group-hover:text-cyan-400 transition-colors duration-300 leading-snug">
                            {{ job.title }}
                        </h2>
                        <p class="text-gray-400 text-xs sm:text-sm font-medium mb-1">{{ job.company_name }}</p>
                        <p class="text-gray-500 text-[10px] sm:text-xs mb-4 sm:mb-6 flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0 text-cyan-500/60" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            <span class="truncate">{{ job.location }}</span>
                        </p>
                    </div>

                    <!-- Job Footer -->
                    <div class="mt-3 sm:mt-4 pt-4 sm:pt-6 border-t border-white/5 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[8px] sm:text-[10px] text-gray-500 uppercase tracking-tighter">Gaji Estimasi</p>
                            <p class="text-sm sm:text-lg font-black text-white leading-none mt-1 truncate">{{ job.salary }}</p>
                        </div>
                        <button 
                            @click="openDetail(job.id)" 
                            class="bg-white/10 hover:bg-cyan-500 hover:text-slate-900 border border-white/10 hover:border-cyan-500 px-3 sm:px-6 py-2 sm:py-3 rounded-lg md:rounded-2xl text-[8px] sm:text-xs font-black transition-all duration-300 whitespace-nowrap flex-shrink-0"
                        >
                            DETAIL
                        </button>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <footer class="mt-12 sm:mt-16 md:mt-20 text-center text-gray-600 text-[8px] sm:text-[10px] uppercase tracking-[0.2em] sm:tracking-[0.3em]">
                &copy; 2026 Dryex Ecosystem • Powered by Laravel Inertia
            </footer>
        </div>

        <!-- Job Detail Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4">
            <div @click="closeModal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

            <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 w-full max-w-2xl max-h-[90vh] overflow-y-auto p-4 sm:p-6 md:p-8 rounded-2xl md:rounded-[3rem] shadow-2xl text-white">
            <!-- Close button -->
            <div class="absolute top-3 sm:top-6 right-3 sm:right-6 z-10">
                <button @click="closeModal" class="text-gray-400 hover:text-white text-xl sm:text-2xl p-1 hover:bg-white/10 rounded-lg transition">✕</button>
            </div>

            <div v-if="selectedJob" class="pr-4 sm:pr-0">
                <span class="text-cyan-400 text-[8px] sm:text-xs font-black tracking-widest uppercase">{{ selectedJob.type }}</span>
                <h2 class="text-2xl sm:text-3xl font-bold mt-2 mb-1">{{ selectedJob.title }}</h2>
                <p class="text-gray-400 text-xs sm:text-base mb-6">{{ selectedJob.company_name }} • {{ selectedJob.location }}</p>
                
                <div class="h-px bg-white/10 mb-6"></div>
                
                <!-- Description -->
                <div class="prose prose-invert max-h-48 overflow-y-auto mb-8 pr-2 sm:pr-4 custom-scrollbar text-gray-300 text-xs sm:text-base">
                    <h4 class="text-white font-bold mb-2 text-xs sm:text-sm uppercase tracking-wider">Deskripsi Pekerjaan</h4>
                    <p class="leading-relaxed">{{ selectedJob.description }}</p>
                </div>

                <!-- Apply Form -->
                <div class="border-t border-white/10 pt-6">
                    <div class="flex flex-col gap-4">
                        <!-- Salary Info -->
                        <div>
                            <p class="text-[8px] sm:text-[10px] text-gray-500 uppercase tracking-tighter">Gaji Estimasi</p>
                            <p class="text-xl sm:text-2xl font-black text-cyan-400 mt-1">{{ selectedJob.salary }}</p>
                        </div>

                        <!-- CV Upload -->
                        <div class="bg-white/5 p-3 sm:p-4 rounded-lg md:rounded-2xl border border-white/10 space-y-3">
                            <label class="block text-[8px] sm:text-[10px] text-gray-400 uppercase mb-2 ml-1">Upload CV (PDF, Max 2MB)</label>
                            <div class="flex flex-col sm:flex-row gap-2 items-end">
                                <div class="flex-grow min-w-0">
                                    <input type="file" 
                                        @input="applyForm.resume = $event.target.files[0]" 
                                        accept=".pdf"
                                        class="text-[9px] sm:text-xs text-gray-300 w-full file:mr-2 sm:file:mr-4 file:py-1 sm:file:py-2 file:px-3 sm:file:px-4 file:rounded-full file:border-0 file:text-[8px] sm:file:text-xs file:bg-cyan-500/20 file:text-cyan-400 hover:file:bg-cyan-500/30 cursor-pointer"
                                    />
                                </div>
                                <button 
                                    v-if="applyForm.resume"
                                    @click="openCVPreview"
                                    class="px-3 sm:px-4 py-1 sm:py-2 bg-white/10 border border-white/10 rounded-lg md:rounded-xl text-[8px] sm:text-[10px] font-bold text-gray-300 hover:text-cyan-400 hover:border-cyan-500/50 hover:bg-cyan-500/10 transition-all uppercase tracking-wider whitespace-nowrap flex-shrink-0"
                                    title="Preview uploaded CV"
                                >
                                    👁 Preview
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button @click="submitApply" :disabled="applyForm.processing || !applyForm.resume"
                            class="w-full bg-cyan-500 disabled:bg-gray-600 disabled:cursor-not-allowed text-slate-900 py-2 sm:py-4 rounded-lg md:rounded-2xl font-black shadow-lg shadow-cyan-500/20 active:scale-95 transition-all uppercase tracking-widest text-[8px] sm:text-sm">
                            {{ applyForm.processing ? 'MENGIRIM...' : 'KIRIM LAMARAN SEKARANG' }}
                        </button>
                    </div>
                </div>
            </div>
            </div>
        </div>

        <!-- CVPreviewModal -->
            <CVPreviewModal 
                :show="showCVPreview"
                :cv-path="previewResumePath"
                candidate-name="Your CV"
                title="Preview CV"
                @close="closeCVPreview"
            />
        </div>
    </UserLayout>
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