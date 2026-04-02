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

// Initialize notification
const { success: showSuccess, error: showError, info: showInfo } = useNotification();

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
            <table class="w-full text-left border-separate border-spacing-y-2">
                <thead>
                    <tr class="text-[10px] font-black text-gray-700 dark:text-gray-400 uppercase tracking-[0.4em] italic">
                        <th class="pb-8 px-6">Candidate</th>
                        <th class="pb-8 px-6">Applied Role</th>
                        <th class="pb-8 px-6">Status</th>
                        <th class="pb-8 px-6 text-right">Date Applied</th>
                        <th class="pb-8 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr v-for="applicant in applicants" :key="applicant.id" class="group hover:bg-white/[0.02] transition-all duration-300 cursor-pointer">
                        <td class="py-7 px-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center font-bold text-white text-sm">
                                    {{ applicant.name?.charAt(0) || 'A' }}
                                </div>
                                <div>
                                    <p class="font-bold text-white text-sm">{{ applicant.name }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ applicant.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-7 px-6">
                            <p class="font-semibold text-white text-sm">{{ applicant.job?.title || 'N/A' }}</p>
                        </td>
                        <td class="py-7 px-6">
                            <span :class="getStatusClass(applicant.status)" class="px-3 py-1 rounded-full text-xs font-bold border inline-block">
                                {{ applicant.status?.charAt(0).toUpperCase() + applicant.status?.slice(1) }}
                            </span>
                        </td>
                        <td class="py-7 px-6 text-right">
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ new Date(applicant.created_at).toLocaleDateString() }}</p>
                        </td>
                        <td class="py-7 px-6 text-right">
                            <div class="flex gap-2 justify-end">
                                <button @click="openCVPreview(applicant)"
                                    class="px-3 py-1.5 bg-blue-500/20 hover:bg-blue-500/40 border border-blue-500/50 rounded-lg text-xs font-bold text-blue-400 transition-all">
                                    📄 CV
                                </button>
                                <button @click="openCoverLetterModal(applicant)"
                                    class="px-3 py-1.5 bg-purple-500/20 hover:bg-purple-500/40 border border-purple-500/50 rounded-lg text-xs font-bold text-purple-400 transition-all">
                                    💬 Surat
                                </button>
                                <select @change="updateStatus(applicant.id, $event.target.value)" 
                                    :value="applicant.status"
                                    class="px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg text-xs font-bold text-white cursor-pointer hover:bg-white/20 transition-all">
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

        <CVPreviewModal 
            v-if="showCVPreview"
            :cv-path="selectedApplicantCV"
            :applicant-name="selectedApplicantName"
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
