<script setup>
import { ref, watch } from 'vue';
import { router, Head, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import axios from 'axios';

// 1. Definisikan Props
const props = defineProps({ 
    jobs: Array,
    filters: Object 
});

// 2. Deklarasikan State (Urutan sangat penting!)
const search = ref(props.filters?.search || ''); // search didefinisikan dulu
const selectedJob = ref(null);
const isModalOpen = ref(false);
const isLoading = ref(false);

// 3. Logika Pencarian (Reaktif terhadap variabel search)
watch(search, debounce((value) => {
    router.get('/', { search: value }, { 
        preserveState: true, 
        replace: true 
    });
}, 300));

// 4. Fungsi Modal & Detail
const openDetail = async (id) => {
    isLoading.value = true; // Diperbaiki dari .ref ke .value
    try {
        const response = await axios.get(`/jobs/${id}`);
        selectedJob.value = response.data;
        isModalOpen.value = true;
    } catch (error) {
        console.error("Gagal mengambil detail job", error);
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
            alert('Lamaran berhasil dikirim ke Dryex!');
        },
    });
};

</script>

<template>
    <Head title="Dryex - Temukan Karir Impianmu" />
    
    <div class="min-h-screen bg-[#0b0f1a] text-white p-6 md:p-12 font-sans relative overflow-hidden">
        
        <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-cyan-500/10 blur-[150px] rounded-full"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[600px] h-[600px] bg-blue-600/10 blur-[150px] rounded-full"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            
            <header class="flex flex-col md:flex-row justify-between items-center mb-16 gap-6">
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
                    >
                    <div class="absolute right-6 top-1/2 -translate-y-1/2 text-cyan-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <div v-if="jobs.length === 0" class="col-span-full text-center py-24 bg-white/5 rounded-[3rem] border border-dashed border-white/10">
                    <p class="text-gray-400 text-xl italic">Wah, lowongan "{{ search }}" belum tersedia saat ini.</p>
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
                                <input type="file" 
                                    @input="applyForm.resume = $event.target.files[0]" 
                                    accept=".pdf"
                                    class="text-xs text-gray-300 w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:bg-cyan-500/20 file:text-cyan-400 hover:file:bg-cyan-500/30 cursor-pointer"
                                >
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