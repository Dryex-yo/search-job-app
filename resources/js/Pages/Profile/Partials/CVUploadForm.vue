<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const page = usePage();
const user = page.props.auth.user;
const fileInput = ref(null);

const form = useForm({
    resume: null,
});

// Watch user changes untuk update display
watch(
    () => page.props.auth.user.resume_path,
    () => {
        // Reset form setelah upload successful
        if (form.recentlySuccessful) {
            form.resume = null;
            form.clearErrors();
        }
    }
);

const selectFile = () => {
    fileInput.value.click();
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file type
        const allowedTypes = ['application/pdf', 'application/msword', 
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if (!allowedTypes.includes(file.type)) {
            form.setError('resume', 'Format file harus PDF atau Word document');
            return;
        }

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            form.setError('resume', 'Ukuran file maksimal 5MB');
            return;
        }

        form.resume = file;
    }
};

const submit = () => {
    form.post(route('profile.upload-resume'), {
        onSuccess: () => {
            form.clearErrors();
            form.resume = null;
            fileInput.value.value = '';
        },
    });
};

const getFileName = () => {
    if (form.resume) {
        return form.resume.name;
    }
    return page.props.auth.user.resume_path ? page.props.auth.user.resume_path.split('/').pop() : 'Belum ada CV';
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                Manajemen CV/Resume
            </h2>

            <p class="text-gray-600 dark:text-gray-300">
                Upload CV Anda di sini. File akan digunakan untuk semua aplikasi pekerjaan Anda
            </p>
        </header>

        <!-- Current CV Display -->
        <div class="mt-6 bg-gray-100 dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">CV Saat Ini</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        📄 {{ getFileName() }}
                    </p>
                </div>
                <div v-if="user.resume_path" class="text-green-600 dark:text-green-400 text-sm">
                    ✓ CV tersimpan
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <form
            @submit.prevent="submit"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="resume" value="Upload CV/Resume Baru" class="text-gray-700 dark:text-white"/>

                <input
                    ref="fileInput"
                    type="file"
                    id="resume"
                    accept=".pdf,.doc,.docx"
                    @change="handleFileSelect"
                    class="hidden"
                />

                <div
                    @click="selectFile"
                    class="mt-2 border-2 border-dashed border-gray-400 dark:border-slate-600 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 dark:hover:border-blue-500 transition-colors bg-gray-50 dark:bg-slate-900"
                >
                    <div class="text-4xl mb-3">📁</div>
                    <p class="text-gray-900 dark:text-white font-medium">
                        {{ form.resume ? form.resume.name : 'Klik atau drag file CV di sini' }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                        Format: PDF atau Word (.pdf, .doc, .docx) • Ukuran maksimal: 5MB
                    </p>
                </div>

                <InputError class="mt-2" :message="form.errors.resume" />
            </div>

            <!-- Tips -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-yellow-700 dark:text-yellow-300 mb-2">💡 Tips untuk CV Terbaik</h3>
                <ul class="text-sm text-gray-700 dark:text-gray-200 space-y-1 list-disc list-inside">
                    <li>Sertakan pengalaman kerja yang relevan</li>
                    <li>Tambahkan skill dan keahlian Anda</li>
                    <li>Pastikan CV terstruktur dan mudah dibaca</li>
                    <li>Update CV secara berkala untuk peluang terbaik</li>
                </ul>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton 
                    :disabled="form.processing || !form.resume" 
                    class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 disabled:opacity-50"
                >
                    Upload CV
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-green-600 dark:text-green-400"
                    >
                        ✓ CV berhasil diupload
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
